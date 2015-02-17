# OUTRAGEdns
This is a nice little drop in replacement, currently in development, for the ugly Poweradmin. If this hurts your installation, please don't come running to me asking for money. I will help you out though.

## Current Features
- It looks nicer?
- Its codebase is a lot cleaner

## Missing Features
- DNSSEC support
- 100% proper validation (but it validates cleanly at the moment)

## Installation
I wouldn't suggest copying and pasting this into your terminal because hey, I haven't tested this out (properly). So, here's something that should help you out if you've starting from fresh:

	mysql -u root --database < app/setup/databases/powerdns.sql
	mysql -u root --database < app/setup/databases/changes.sql
    composer install -o
    
    cp app/etc/config/database.json.example app/etc/config/database.json
    vi app/etc/config/database.json

Feel free to use the argument-settling text editor of your choice.

If you currently have a PowerDNS installation, please only install `changes.sql`.
If you currently have a PowerDNS and Poweradmin installation, bless you, and please open `changes.sql` and insert into your database the bit below `-- OUTRAGEDNS BLOCK --`.

## Logging in for the first time:
 - User: `admin`
 - Pass: `ifacetherisk`

## Updating things and clearing the cache
    composer update -o

## Licensing
I think that's all I need to note at the moment. Oh, and the licence would have been GPL because I thought, hey, let's use some ideas from the Poweradmin repo but since no real specific code actually was transferred, I've chosen to offer it under the MIT/Expat Licence (included in the repo) - and if you want it under something such as the WTFPL then feel free to ask.