FROM ubuntu:14.04
MAINTAINER PAUL JUNG <porys@hotmail.com>

# Set the locale
RUN locale-gen en_US.UTF-8  
ENV LANG en_US.UTF-8  
ENV LANGUAGE en_US:en  
ENV LC_ALL en_US.UTF-8 

RUN sed -i 's/archive.ubuntu.com/ftp.daum.net/g' /etc/apt/sources.list

RUN apt-get update
RUN apt-get install -y apache2 php5 unzip wget

RUN wget "https://github.com/Studio-42/elFinder/archive/2.1.9.zip" -O /var/www/html/elfinder.zip \
	&& unzip /var/www/html/elfinder.zip -d /var/www/html/ \
	&& cp -rf /var/www/html/elFinder-2.1.9/* /var/www/html \
	&& rm -rf /var/www/html/elFinder-2.1.9 \
	&& rm /var/www/html/elfinder.zip \
	&& rm /var/www/html/index.html

VOLUME /var/www/data
COPY 000-default.conf /etc/apache2/sites-available/
COPY connector.minimal.php /var/www/html/php/
COPY index.html	/var/www/html/
COPY elfinderBasicAuth.js /var/www/html/js/

RUN  sed -i 's/Indexes//g' /etc/apache2/apache2.conf


EXPOSE 80

