# afeefa/docker-nginx-frontproxy

An nginx proxy server to run local projects using custom domain names and autogenerated ssl certificates.

```bash
# add domain to /etc/hosts
127.0.0.1 my-project.test

# add server
./nginx-frontproxy add-server my-project.test my-project

# start front proxy
docker-compose up -d

# start my-project
cd my-project
docker-compose up -d

# open browser
firefox https://my-project.test
```


## Description

Having multiple projects running the same time on different ports on localhost can become quite confusing. Which port serves which project? How to manage a bigger number of projects?

```bash
localhost:8080 # project 1
localhost:8081 # project 2
...
localhost:808(n-1) # project n
```

We can solve the project identification problem by creating local domain names. We can solve the port distribution problem by running the projects each inside a docker container.

```bash
project1.test # -> project-1-docker:80
project2.test # -> project-2-docker:80
...
projectn.test # -> project-n-docker:80
```

To access these projects from the host machine, e.g. via `http://project1.test`, we need now a proxy server, that maps (proxies) our local domains to docker containers.

`docker-nginx-frontproxy` is such a proxy server that:

* runs in a docker container and listens on hosts ports 443 and 80
* provides a cli to create or remove proxy configurations
* autogenerates ssl certificates for each domain added

## Architecture

* The host defines a number of local domains in its `/etc/hosts` file
* The front proxy consists of a vhost.conf for each of these local domains
* Any http request to port 80 or 443 on the host gets delegated to the front proxy server
* The front proxy passes the request further to the appropriate projects docker services

![architecture](https://raw.githubusercontent.com/afeefacode/docker-nginx-frontproxy/main/docs/architecture.png "architecture")

## Installation

### Requirements

* Docker and docker-compose
* PHP >=7.4 for the cli tool
* PHP composer to install the cli tool

### Install

```bash
# 1. checkout the project
git clone git@github.com:afeefacode/docker-nginx-frontproxy.git
cd docker-nginx-frontproxy
# 2. install the cli
composer install
# 3. setup ssl and localhost
./nginx-frontproxy setup
# 4. start the front proxy
docker-compose up
```

The cli `setup` command will create a certificate authority which is later used to generate ssl certificates for our domains. In order to have the browser accept those certificates, you need to import the certificate authority into your particular browser.

![import-ca](https://raw.githubusercontent.com/afeefacode/docker-nginx-frontproxy/main/docs/import-ca.png "import-ca")

The location of the generated ca file to import is: `docker-nginx-frontproxy/servers/ca/ca.pem`.

## Adding a server

Just pass a local domain name and a corresponding docker container name. You may specify a port (if other than 80).

```bash
./nginx-frontproxy add-server my-domain.test my-domain-service
./nginx-frontproxy add-server my-other-domain.test my-other-domain-service:8080
```

It's also possible to proxy a non docker localhost service:

```bash
./nginx-frontproxy add-server my-nondocker-domain.test localhost:8080
```


Don't forget to add the domain to your local `/etc/hosts` file.

```bash
127.0.0.1 localhost
127.0.0.1 my-domain.test # <-- added
127.0.0.1 my-other-local-domain.test
```

You might use the [hostess](https://github.com/cbednarski/hostess) tool.

```
hostess add my-domain.test 127.0.0.1
```

## Removing a server

```bash
./nginx-frontproxy remove-server my-domain.test
```

Don't forget to remove the domain from your local `/etc/hosts` file.

You might use the hostess tool (see above).

```bash
hostess rm my-domain.test
```

## Handling more complex projects

A project may consist of multiple sub services (frontend, backend, websockets, ...) which all should be publicly accessed. In this case it's common to put a local nginx proxy at project level in front of those sub services and provide access via custom url.

```nginx
location /frontend/sockjs-node/ { # browsersync
    proxy_pass vue:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
}

location /frontend { # vue dev server
    proxy_pass vue:8080;
}
```

Yes, this will make up a chain of two proxy servers (front, project).

## Example of a more complex project

You can find an example project configuration in the `èxample` folder. The project consists of two node services as well as a PHP api. The project utilizes an internal nginx web server to bundle all services together. To run the example you need to setup `docker-nginx-frontproxy` first. Then:

```bash
cd example
docker-compose up
```

The project's nginx instance exposes (for testing purposes) an accessible port 8080, so you can just open firefox and request `http://localhost:8080`.

![localhost8080](https://raw.githubusercontent.com/afeefacode/docker-nginx-frontproxy/main/docs/localhost8080.png "localhost8080")

Note, the secure http version does not work here - you'll see when trying to call `https://localhost:8080`. SSL is configured not at project but at front proxy level.

![localhost8080ssl](https://raw.githubusercontent.com/afeefacode/docker-nginx-frontproxy/main/docs/localhost8080ssl.png "localhost8080ssl")

Setting up a domain name and a secure connection is now just two commands. Let's call the example project `docker-frontproxy-example.test`

```bash
# add domain to /etc/hosts
sudo hostess add docker-frontproxy-example.test 127.0.0.1

# add server to front proxy
# use the nginx container name from docker-compose.yml
./nginx-frontproxy add-server docker-frontproxy-example.test nginx-frontproxy-example-nginx
```

Open firefox with `https://docker-frontproxy-example.test` and you can visit the same example project wrapped with `docker-nginx-frontproxy` and ssl enabled.

![docker-frontend-example-test](https://raw.githubusercontent.com/afeefacode/docker-nginx-frontproxy/main/docs/docker-frontend-example-test.png "docker-frontend-example-test")
