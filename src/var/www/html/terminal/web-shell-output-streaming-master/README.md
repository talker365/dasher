Experiment - Streaming Terminal Output with Output Streaming
============================================================

Goal: 
----
Execute a shell command and display the output to the browser. This is
intended to handle shell commands without a definite end, such as `$ ping www.google.com`.
The output for these commands should be streamed to the browser as they are available.

Strategy:
---------
PHP and web servers buffer output so that it can be sent to the browser in larger chunks
and therefore compressed to reduce the size of the data going over the wire. By disabling this,
output from shell commands can be sent to the browser without being held back for any amount
of time.

Pros
----
1. Simple code to implement.
2. Output is sent _very_ quickly.

Cons
----
1. Requires tedious web server configuring.
2. Data is difficult to structure and requires an `<iframe>` to be rendered in.
3. Output cannot be compressed.
4. The servicing endpoint is left open for as long as the terminal output is being streamed.
5. Requires checking the filesystem or other globally accessible environment to know when to stop.

Notes:
------
My number 1 pain point dealing with configuration. It's
difficult and tedious to debug both Nginx's output buffer and PHP-FPM's own output buffering settings.
I was able to go about testing using PHP's built in webserver and disabling output buffering in the
CLI php.ini. This was great for streaming the output, but in order to _stop_ the terminal command
you would need to issue _another_ HTTP request to the same server to set an artifact for the first
endpoint to pick up on. Because the output endpoint is still streaming output, it blocks the second
HTTP request from being processed and stopping the first.

I got around this by using both PHP's built in webserver and Nginx. PHP handled streaming the output on port 80,
while Nginx would listen on port 81 for the termination request. You get CORS issues because the ports are different.
I emulated the second HTTP request in Postman for this to avoid having to handle even more werid edge cases.

I'm fairly confident someone better at configuring Nginx/Apache could have avoided this and gotten to a
perfectly functioning experiment, but the result I came to was close enough to form an opinion that
this strategy has enough problems that it isn't worth pursuing.

On a separate issue, the data is being streamed out is difficult to work with. The data coming back won't be a syntactically
valid document until the entire script has terminated. An opening `<html>` element could be created with each line 
of output being put before the ending `</html>`. This is problematic because the amount of lines and the amount of time
to stream the lines is unknown and ideally determined by the end-user. The only solution I found was to make the request
in an `<iframe>` and allow the streamed lines to be unstructured. This is a massive issue that would become more
apparent in a more established setup.

Lastly, the outputting process cannot accept more user input on it's own so it must check some globally available 
system resource to know when to stop. I used a file for this. When end.txt was present, the output process would stop.
But this requires constantly polling for that file. This is kind of annoying, but not the end of the world.
