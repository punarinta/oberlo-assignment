# Test assignment for Oberlo

## API description

### Authentication

The assignment asked for a basic HTTP authentication, so simply add an _Authorization_
header equal to `Basic some_hash`, where that hash is base64 of `your_username:your_password`.


### Methods

* GET /messages — show all messages
* GET /messages/archived — show archived messages
* GET /messages/:id — show a particular message
* PUT /messages/:id/read — mark message as read
* PUT /messages/:id/archive — mark message as archived


### Pagination

To paginate apply GET parameters `page_start` and `page_length`. If `page_length` is
missing pagination is considered to be off. If more data is present on the next pages
`Link` header will be sent according to RFC 5988.


## Installation

I assume you have PHP, Nginx an MySQL already installed and everything is running
on a Debian-like Linux machine. If not, well, anyway you know what to do. :P

1. Copy file `App/config.dist.php` to `App/config.php` and adjust fields in the latter.
2. Import schema to create a database and a table
`mysql -u username -p'password' < docs/schema.sql`
3. Add `127.0.0.1 api.local-firm.dev` line to `/etc/hosts` file.
4. Add a web server config:

```$
server
{
  listen 80;
  server_name api.local-firm.dev;
  root /path/to/public/folder;
  index index.php;

  try_files $uri $uri/ /index.php?$query_string;

  location ~ \.php$
  {
    fastcgi_pass unix:/path/to/php/socket/file;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param PATH_INFO $fastcgi_script_name;
    include /etc/nginx/fastcgi_params;
  }
}

```
5. Reload Nginx with `service nginx reload`.
6. Assure CLI file is executable by `chmod +x ./cli` (or simply run it as `php cli`
next time you need it).


## Testing
The API is supposed to be up before testing. Run `./cli test all` to perform all tests.
The CLI will both display its log and return a corresponding return code that can
be used further. Tests are supposed to be run in development environment and their
performance is not always optimized for production.


## Evaluation

1. Get some tool to work with APIs, like [Postman](https://www.getpostman.com).
2. Run `./cli admin populate docs/messages_sample.json` to populate the database.
3. ...
4. PROFIT!
