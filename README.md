# Debt Reminder Bot

Debt Reminder Bot is based on database from Google Sheets and Telegram Bot.

## Installation

Create new Telegram Bot and replace bot's token in bot.php

Create new Google Sheets and make sure that your data is equivalent to the template CSV I made in the folder. Then publish it on Web as CSV, you have CSV link. Replace that link in bot.php

Replace your telegram user ID in bot.php


```php
$spreadsheet_url = "<...csv>"; // string
$token = "..."; // string
$user_id = ...; // number
```

## Usage

Run cronjob ..../bot.php daily (24h)

```bash
25 7 * * * curl -s "http://domain.com/bot.php"
```


## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
This license belongs to 
[storytellar](https://github.com/storytellar) !
