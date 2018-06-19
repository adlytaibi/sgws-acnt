# StorageGRID Webscale Accounting: Quota and Disk Usage

This is a php-based code that provides a web-interface to show a list of tenants, quota and disk usage in a read-only view.
The workflow is simple, you provide login credentials and API endpoint. RESTful API calls are made on your behalf and the result is presented in a table with pagination, sort and filter features.

  ![](https://raw.githubusercontent.com/adlytaibi/ss/master/sgws-acnt/table.png)

## Pre-requisites

* git
* docker
* docker-compose

## Installation

1. Clone this:

    ```
    git clone https://github.com/adlytaibi/sgws-acnt
    ```

    ```
    cd sgws-acnt
    ```

2. SSL certificates

    ```
    mkdir web/sslkeys
    ```

* Copy your host.pem and host.key certificate files to web/sslkeys

* (Optionally) Self-sign your own certificates (modify `web` to match your server)

    ```
    openssl req -x509 -nodes -newkey rsa:4096 -keyout web/sslkeys/host.key -out web/sslkeys/host.pem -days 365 -subj "/C=CA/ST=Ontario/L=Toronto/O=Storage/OU=Team/CN=web"
    ```

3. docker-compose

    ```
    docker-compose up -d
    ```

4. The login page can be accessed using the URL below:

    ```
    https://<IP_address>
    ```
	(or if accessing from the same guest https://localhost)

  ![](https://raw.githubusercontent.com/adlytaibi/ss/master/sgws-acnt/login.png)

5. Enter the API endpoint which typically your GMI (Grid Management Interface) or Admin node's hostname or IP_address. The user you login with requires a minimum of `Tenant Accounts` group management permission.

  ![](https://raw.githubusercontent.com/adlytaibi/ss/master/sgws-acnt/endpoint_entry.png)

  ![](https://raw.githubusercontent.com/adlytaibi/ss/master/sgws-acnt/endpoint_saved.png)

6. Sort, filter, pagination and logout to clear API authorization

  ![](https://raw.githubusercontent.com/adlytaibi/ss/master/sgws-acnt/table.png)

## Further reading
* [Docker Compose](https://docs.docker.com/compose/)
* [Apache](https://httpd.apache.org/)
* [PHP](http://www.php.net/)
* [DataTables](https://datatables.net/)
* [Bootstrap](https://getbootstrap.com/)
* [jQuery](https://jquery.com/)
* [Httpful](https://github.com/nategood/httpful)

## Notes
This is not an official NetApp repository. NetApp Inc. is not affiliated with the posted examples in any way.

