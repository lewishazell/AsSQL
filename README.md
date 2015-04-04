# AsSQL
A library which aims to make asynchronous MySQL queries in PHP easier.

## Why did you create this?
PHP supports asynchronous MySQL queries with MySQLi (that is, non-blocking asynchronous I/O on the sockets). It's a nice feature; but I found it difficult to manage, having to poll all waiting connections. The aim with this is to be able to enable the programmer to begin the query with a specified anonymous callback function which will be automatically called once the query is complete and polled.

## Details you need to know
This library requires *mysqlnd* (mysql native driver), as asynchronous MySQL is not supported in the default driver!

Ideally, we'd have a second thread which waits for activity on all of the connections. Unfortunately, we don't have the luxury of userland threads in PHP (unless you want to try pthreads and make this threadsafe - go ahead). Because of this, you will need to have something in your code which will periodically check the status of the queries.

At the moment I can think of two ways in which this can be achieved, let me know if there are more. The best way will be specific to your application.

The first is with loops:
```php
while(true) {
  $objServer->run();
  PollPool::poll();
}
```
Here, the (hypothetical) server will try to read data from users using `socket_select` and process any packets received. This packet may trigger an asynchronous SQL query, which will run in the background whilst other packets are being handled. Once these other packets are handled, `PollPool::poll()` will poll all queries made for results; this way, the whole server will not be waiting on SQL queries going to the server and back.

The second is with a tick function:
```php
declare(ticks=1);
register_tick_function(array('PollPool', 'poll'));
```
This may be more useful in a more sequential program where there isn't a main running loop.

## How do I make asynchronous MySQL queries?
Good question!

First, you start by creating your connection:
```php
try {
    $objSQL = new AsSQL('localhost', 'user', 'secrets', 'db');
}catch(mysqli_sql_exception $objException) {
    die($objException->message);
}
```
Then you want to make an anonymous callback function, which will be called once the query is complete (we'll get to the query itself after..):
```php
$funcCallback = function($objAsyncResult) {
                    $objSQL = $objAsyncResult->getTag(); // Our AsSQL instance
                    try {
                        $objResult = $objSQL->endQuery($objAsyncResult); // Free up the instance for queries and get the result.
                        var_dump($objResult->fetch_row()); // Output the result.
                        $objResult->free(); // Free resources
                        die("Done!"); // We're done!
                    }catch(mysqli_sql_exception $objException) { // Exceptions can be thrown by AsSQL::endQuery, be careful!
                        die($objException->message); // Uh oh.. :(
                    }
                };
```
You'll notice `$objAsyncResult->getTag()`, this is a "Tag" value, which can be anything, that you wish for your callback to function to have; this is given when you create the query. In this case, it's the AsSQL instance but it could be an array, integer, object, anything..

Now creating the query:
```php
$objSQL->beginQuery(
        'SELECT name, surname FROM customers WHERE CID=11', // Our query
        $funcCallback, // Our callback function
        $objSQL // The AsSQL instance, our "tag" object, which we wish the callback function to have
    );
```
Once this query is completed and polled `$funcCallback`, as defined earlier, will be called.

## Can I still do my regular synchronous queries?
Yes! Like so:
```php
$objResult = $objSQL->getConnector()->query('SELECT name, surname FROM customers WHERE CID=11');
```
