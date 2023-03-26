# use mariadb for running on Apple M1 (was "mysql:5.6")
FROM mariadb

RUN apt-get update && \
    apt-get install -y less

# Setup a non-UTC timezone to simulate where servers are & proper handling of date/times
#  America/Rainy_River   CET   UTC-06
ENV TZ=America/Rainy_River
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
