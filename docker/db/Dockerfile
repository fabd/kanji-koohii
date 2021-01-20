FROM mysql:5.6

RUN apt-get update && \
    apt-get install -y less

# Cursor Up/Down to browse command history, Cursor Left/Right to move between command args
RUN cd /root \
    && printf '"\e[A": history-search-backward\n"\e[B": history-search-forward' > /root/.inputrc \
    && printf '\n\n"\e[1;5D": backward-word\n"\e[1;5C": forward-word' >> /root/.inputrc
