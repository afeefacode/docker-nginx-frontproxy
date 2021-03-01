FROM nginx

RUN apt-get update; \
  apt-get install -y \
  iputils-ping

RUN rm /etc/nginx/conf.d/default.conf
RUN echo "include /servers/vhosts/*.conf;" > /etc/nginx/conf.d/vhosts.conf
