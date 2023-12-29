# CivilDrive

Written as a concept because I was bored. It works. Here's basic setup documentation:


Required Dependencies:

- MinIO
- Composer
- Composer aws\aws-sdk-php
- Composer Nette\Mail
- An Email Server (Required, or you will need to manually edit database user accounts to "verify" them)

Installation

We're assuming you're running a Debian-Based distro in this guide (Such as Ubuntu) 

Install Dependencies - This assumes you already have a webserver, php8.1 and MySQL installed:

`sudo apt update && sudo apt install -y composer`


Install MinIO for your Linux Distro, we are assuming Debian-Based in this guide: https://min.io/docs/minio/linux/index.html

After installing Minio on the same server that you'll be hosting the website on:

Log into Minio: http://your-server-ip:9000
Create an API Key, make sure to save the Key & Secret.

If you do not have one already, create a database with MySQL. 
Navigate to http://your-server-ip/setup.php and fill out the form to setup the server. You will be asked for a database address, your API key, secret and email server details.

Once everything is inputted, you should be able to upload files via the webpage and have them be stored in minio. 

Please note, this guide does not cover the usage of Minio, you will need to do additional configuration to make Minio automatically start after a restart, and more. 

We offer no support for this code and we plan on redesigning it for production use at a later date. If you need a storage server with unlimited fair use bandwidth, contact us @ support@civilhost.net
