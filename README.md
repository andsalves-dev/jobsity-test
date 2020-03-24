## Jobsity Test
### Chatbot

#### Stack Details
- PHP Framework Used: Symfony
  - Other back-end tools/libraries: Composer, Doctrine ORM, JWT Auth, Symfony caching...
  - Currency API: Fixer.io
- Front-end: A single twig template file is used as entry point. 
VueJs, Axios, Bootstrap, and moment.js were also used to facilitate UI interactions and integration.

#### Prerequisites:
- PHP 7.2+
- mysql-server
    - `sudo apt install mysql-server5.7`
       (ubuntu/debian)
- Composer
    - https://linuxize.com/post/how-to-install-and-use-composer-on-ubuntu-18-04/
- Symfony cli
    - https://symfony.com/download
- Git

#### Steps to install and run:
Clone the project:
```
git clone https://github.com/andsalves-dev/jobsity-test.git
cd jobsity-test
```

Create a .env.local file, and put an entry for the database config:
```
# For example:
DATABASE_URL=mysql://root:root@127.0.0.1:3306/jobsity_test?serverVersion=5.7
```

Now, run:
```
composer install                              # Installs php dependencies
php bin/console doctrine:database:create      # Creates the working database
php bin/console doctrine:migrations:migrate   # Runs migrations (y)
```
Finally, start the server with
```
composer start
```

On your browser, access `http://localhost:8000` to check the page.

API routes go with the '/api' prefix.

## Bot commands
The bot can execute a few tasks, using the following patterns:

#### Register / Login:
- Once you enter the page, and you're not logged in, the bot will ask you to enter 'register' or 'login'.
After that, you will be walked through the respective wizard.
You can use your username and password to login once registered.

Default credentials:
`username: andsalves, passwd: 123456`

#### For logged user:
- Deposit an amount - pattern: "Deposit" + numerical amount + currency
  - Examples:
    - Deposit 1000 USD
    - Please deposit 40 USD
    - Can I deposit 1.99 USD, please?
- Withdraw an amount - Follows the same pattern of deposits, but using "withdraw" instead of "deposit".
- Check balance
    - Examples:
        - Show balance
        - Check balance
        - Balance
- Convert an amount between currencies
    - Examples: 
      - What's 100 USD in BRL
      - USD to BRL
      - Convert 100 EUR to USD
- Exit/Logout
  - Run 'Logout', 'Quit' or 'Exit' to perform a logout.
- Clear messages list:
  - Type 'clear' to clear the messages.
  
*You can also click on '(?) see available commands' link below the text box to see a list of available commands.

Please send me a message if you'd like me to answer any question about the project.
