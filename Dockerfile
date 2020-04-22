FROM lefuturiste/php:7.4
LABEL maintainer="contact@thingmill.fr"
ADD . /app
WORKDIR /app
RUN composer install
# default envs vars
ENV JOBATATOR_HOST jobatator
ENV JOBATATOR_PORT 8962
ENV JOBATATOR_USERNAME root
ENV JOBATATOR_PASSWORD root
ENV JOBATATOR_GROUP retrobox
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
CMD ["php", "worker.php"]
