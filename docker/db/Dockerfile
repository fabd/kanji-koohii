FROM mysql:5.6

RUN apt-get update && \
    apt-get install -y less

# Cursor Up/Down to browse command history, Cursor Left/Right to move between command args
RUN cd /root \
    && printf '"\e[A": history-search-backward\n"\e[B": history-search-forward' > /root/.inputrc \
    && printf '\n\n"\e[1;5D": backward-word\n"\e[1;5C": forward-word' >> /root/.inputrc

# Setup a non-UTC timezone to simulate where servers are & proper handling of date/times
#  America/Rainy_River   CET   UTC-06
ENV TZ=America/Rainy_River
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
