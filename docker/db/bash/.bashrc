# mysql container aliases

export HISTFILESIZE=10000
export HISTSIZE=10000
export HISTCONTROL=ignoreboth

###############
# CONVENIENCE
###############

# prompt
export PS1="\[\033[01;03;44m\][mysql]\[\033[01;31;49m\] ${debian_chroot:+($debian_chroot)}\[\033[01;33m\]\u\[\033[01;32m\] \w \[\033[00m\]\$ "

# control-b and control-f to search bash history
bind '"\C-b": history-search-backward'
bind '"\C-f": history-search-forward'

# auto-complete cycle through suggestions
bind TAB:menu-complete

# make ~/doc complete to ~/Documents
bind "set completion-ignore-case on"

# show all completions after pressing tab once instead of twice
bind "set show-all-if-ambiguous on"

# aliases
alias l='ls -lha'


###############
# KOOHII DEV
###############

alias koohii-db='mysql -u koohii -pkoohii -h localhost -D db_koohii --default-character-set=utf8 --local-infile=1'

cd /etc/mysql

