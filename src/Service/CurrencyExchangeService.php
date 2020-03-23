<?php

namespace App\Service;

use App\Exception\CurrencyConversionException;
use App\Util\Requester;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class CurrencyExchangeService {

    /** @var string */
    private $apiKey;
    /** @var string */
    private $apiUrl;
    /** @var FilesystemAdapter */
    private $cacheAdapter;

    /**
     * @param float $amount
     * @param string $from Origin currency
     * @param string $to Target currency
     * @return float the converted amount value
     * @throws CurrencyConversionException
     * @throws InvalidArgumentException
     */
    public function convertToCurrency(float $amount, string $from, string $to): float {
        if (!$this->validateCurrency([$from, $to], $invalid)) {
            throw new CurrencyConversionException(sprintf(
                'Attempt to perform conversion between one or more invalid currencies: %s',
                implode(', ', $invalid)
            ));
        }

        $cacheKey = 'currency_rates';

        $rates = $this->cacheAdapter->get($cacheKey, function (ItemInterface $item) {
            $item->expiresAfter(strtotime('30 minutes', 0));
            $response = Requester::makeRequest("{$this->apiUrl}/latest", ['access_key' => $this->apiKey]);

            if (!$response || !$response['success']) return [];

            return is_array($response['rates']) && count($response['rates']) ? $response['rates'] : [];
        });

        $this->handleArrayDataRetrieveIssue($rates, $cacheKey);

        if (!array_key_exists($from, $rates) || !array_key_exists($to, $rates)) {
            $this->cacheAdapter->delete($cacheKey);
            throw new CurrencyConversionException(self::API_CONNECTION_MESSAGE);
        }

        if (floatval($rates[$from]) < PHP_FLOAT_EPSILON) { // is zero?
            $this->cacheAdapter->delete($cacheKey);
            throw new CurrencyConversionException(sprintf(
                'We had a problem while performing the conversion, the rate cannot be zero.'
            ));
        }

        $interRate = floatval($rates[$to]) / floatval($rates[$from]);

        return round($amount * $interRate, 2);
    }

    /**
     * Check whether a currency is valid or not.
     * Returns true for valid currency, false otherwise
     *
     * @param string|array $currency
     * @param array $invalid
     * @return bool
     * @throws CurrencyConversionException
     * @throws InvalidArgumentException
     */
    public function validateCurrency($currency, ?array &$invalid = []): bool {
        if (is_string($currency) && strlen($currency) !== 3) return false;

        $currencies = is_array($currency) ? $currency : [$currency];
        $allValidCurrencies = $this->getValidCurrencies();
        if (!is_array($invalid)) $invalid = [];

        return array_reduce($currencies, function ($result, $nextCurrency) use ($allValidCurrencies, &$invalid) {
            if (!($isValid = array_key_exists($nextCurrency, $allValidCurrencies))) {
                $invalid[] = $nextCurrency;
            }

            return $result && $isValid;
        }, true);
    }

    /** @return string[]
     * @throws InvalidArgumentException
     * @throws CurrencyConversionException
     */
    private function getValidCurrencies(): array {
        $cacheKey = 'valid_currencies';

        $currencies = $this->cacheAdapter->get($cacheKey, function () {
            $response = Requester::makeRequest("{$this->apiUrl}/symbols", ['access_key' => $this->apiKey]);

            if (!$response || !$response['success']) return [];

            return is_array($response['symbols']) && count($response['symbols']) ? $response['symbols'] : [];
        });

        $this->handleArrayDataRetrieveIssue($currencies, $cacheKey);

        if (!array_key_exists('USD', $currencies)) { // validating array
            $this->cacheAdapter->delete($cacheKey);
            return [];
        }

        return $currencies;
    }

    /**
     * @param $data
     * @param string $cacheKey
     * @throws CurrencyConversionException
     * @throws InvalidArgumentException
     */
    private function handleArrayDataRetrieveIssue($data, string $cacheKey): void {
        if (!is_array($data) || !count($data)) {
            $this->cacheAdapter->delete($cacheKey);
            throw new CurrencyConversionException(self::API_CONNECTION_MESSAGE);
        }
    }

    public function __construct() {
        $this->apiKey = $_ENV['FIXERIO_API_KEY'];
        $this->apiUrl = $_ENV['FIXERIO_API_URL'];
        $this->cacheAdapter = new FilesystemAdapter();
    }

    private const API_CONNECTION_MESSAGE = 'Could not establish connection with the currency conversion API';
}