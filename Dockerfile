FROM nginx

RUN apt-get update; \
  apt-get install -y \
  iputils-ping

RUN rm /etc/nginx/conf.d/default.conf
RUN echo "include /servers/vhosts/*.conf;" > /etc/nginx/conf.d/vhosts.conf

# Vite dev servers load hundreds of modules per page. With the default
# limit of 1000 requests nginx closes the HTTP/2 connection during the
# second page load, and some requests stay pending in the browser.
RUN echo "keepalive_requests 100000;" > /etc/nginx/conf.d/keepalive.conf
