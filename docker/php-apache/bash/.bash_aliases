# php-apache container aliases

export HISTFILESIZE=10000
export HISTSIZE=10000
export HISTCONTROL=ignoreboth

###############
# CONVENIENCE
###############

# prompt
export PS1="\[\033[01;03;42m\][php]\[\033[01;31;49m\] ${debian_chroot:+($debian_chroot)}\[\033[01;33m\]\u\[\033[01;32m\] \w \[\033[00m\]\$ "

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
# PHP & SYMFONY
###############

# symfony CLI
alias sf='php lib/vendor/symfony/data/bin/symfony'

# watch error log
phperrlog() { tail -f /var/log/apache2/error.log ; }

# watch two logs?
#tail -F /var/log/apache2/other_vhosts_access.log /var/log/apache2/error.log


###############
# KOOHII DEV
###############

# build / alias for favicon generator (see src/web/favicons/README.md)
alias real-favicon=./node_modules/.bin/real-favicon

# admin / createuser
createuser() { php batch/maintenance/createUser.php $@ ; }

# admin / edituser
edituser() { php batch/maintenance/__edituser.php $@ ; }

# cd to symfony project root folder
cd /var/www/html
