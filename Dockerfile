FROM debian
LABEL maintainer="spamfree@matthieubessat.fr"
ADD . /app
WORKDIR /app
RUN apt-get update && apt-get -y upgrade
# install php
RUN apt-get install -y apt-transport-https lsb-release ca-certificates wget
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list
RUN apt-get update && apt-get -y upgrade
RUN apt-get -y install curl
RUN apt-get -y install php7.2 php7.2-common php7.2-cli php7.2-fpm php7.2-zip php7.2-xml
RUN apt-get -y install php7.2-common php7.2-json php7.2-curl php7.2-mbstring php7.2-bcmath php7.2-mysql
RUN apt-get -y install composer
RUN apt-get -y install unzip zip
RUN composer install
RUN chmod -R 777 /app
# default envs vars
ENV RABBITMQ_HOST rabbitmq
ENV RABBITMQ_PORT 5672
ENV RABBITMQ_USERNAME root
ENV RABBITMQ_PASSWORD root
ENV RABBITMQ_VIRTUAL_HOST retrobox
ENV FTP_HOST ftp
ENV FTP_PORT 21
ENV FTP_SSL true
ENV FTP_USERNAME root
ENV FTP_PASSWORD root
ENV FTP_DIRECTORY /path/to/directory
ENV API_KEY XXXXXX
ENV API_ENDPOINT https://api.retrobox.tech
ENV DATA_ENDPOINT https://data.retrobox.tech
ENV SMTP_HOST example.org
ENV SMTP_PORT 587
ENV SMTP_SECURE tls
ENV SMTP_USERNAME email@example.com
ENV SMTP_PASSWORD XXXXXX
#ENV ELASTICSEARCH_ENDPOINT http://elasticsearch:9200
#ENV ELASTICSEARCH_INDEX notepader
# run
CMD php worker.php
