# --------------------------------------------------------------------
# Bash config
# --------------------------------------------------------------------

export HISTFILESIZE=10000
export HISTSIZE=10000
export HISTCONTROL=ignoreboth

isApacheService() {
  if [ -d "/var/lib/apache2" ]; then
    return 0 # directory exists, return true
  else
    return 1 # directory does not exist, return false
  fi
}

if isApacheService; then

  export PS1="\[\033[01;03;42m\][php]\[\033[01;31;49m\] ${debian_chroot:+($debian_chroot)}\[\033[01;33m\]\u\[\033[01;32m\] \w \[\033[00m\]\$ "

else

  export PS1="\033[33m(db) \033[1;31m\u\033[0;32m \033[1;34m\w $ \033[0m"

  # a colored prompt for readability ( https://dbdemon.com/mariadb_cli_with_colours/ )
  export MYSQL_PS1=$'\001\033[1;31m\002\\u \001\033[1;34m\002\\d >\001\033[00m\002 '

fi

# also make Cursor Up/Down complete command
bind '"\e[A": history-search-backward'
bind '"\e[B": history-search-backward'

# Auto-complete cycle through suggestions
bind 'TAB:menu-complete'

# Disable distracting/confusing highlight of pasted text in terminal
bind 'set enable-bracketed-paste off'

# make ~/doc complete to ~/Documents
bind 'set completion-ignore-case on'

# show all completions after pressing tab once instead of twice
bind 'set show-all-if-ambiguous on'

alias l='ls -ahl --color --group-directories-first'

# IPv4
ip() {
  MYIP=$(curl -s ifconfig.me)
  #echo -n $MYIP | pbcopy     # (macOS)
  echo -n $MYIP | xsel -b # (Ubuntu)
  echo "IP Address copied to clipboard ($MYIP)"
}

# --------------------------------------------------------------------
# Koohii CONFIG
# --------------------------------------------------------------------

KK_APP=/var/www/html
KK_WEB=/var/www/html/web

# ====================================================================
# Koohii DEV ONLY
# ====================================================================

if isApacheService; then

  # npm tools
  alias sass='./node_modules/.bin/sass'
  alias stylelint='./node_modules/.bin/stylelint'
  alias vite='./node_modules/.bin/vite'
  alias vue-tsc='./node_modules/.bin/vue-tsc'

  # npm-check-updates very handy to upgrade packages
  alias ncu='./node_modules/.bin/ncu'

  # favicon generator (see src/web/favicons/README.md)
  alias real-favicon='./node_modules/.bin/real-favicon'

  # php-cs-fixer
  #
  #   Install : https://cs.symfony.com/#installation
  #
  alias phpcs='tools/php-cs-fixer/vendor/bin/php-cs-fixer'

  # psysh
  #
  #   Install : https://psysh.org/
  #
  alias psysh='./psysh'

fi

######################################################################
# PROD SERVER ONLY
######################################################################

# ...

# ====================================================================
# Koohii DEV/PROD  (keep this last part in sync with the prod server)
# ====================================================================

if isApacheService; then

  # ------------------------------------------------------------------
  # php/Apache container
  # ------------------------------------------------------------------

  alias kk="cd $KK_APP"
  alias kkweb="cd $KK_WEB"

  # symfony CLI
  alias sf='./symfony'

  # clear the config only (not the study pages etc!)
  alias sf-cache-clear-config='sf cache:clear --type=config'

  # configs
  kkphpini() { vim /etc/php/7.4/apache2/conf.d/koohii.php.ini; }

  # where the php error_log() output goes to
  kkphplog() { tail -f /var/log/apache2/error.log; }

  # custom log with colored output (see lib/ColorizedLogger.php)
  kklog() { tail -f koohii-log.txt; }

  # test email sending
  alias kkmailtest="php $KK_APP/batch/maintenance/mailtest.php"

  # website maintenance (locally testing the "website unavailable" page)
  alias kkdis='sf project:disable koohii dev'
  alias kkena='sf project:enable koohii dev'

  # admin misc
  createuser() { php batch/maintenance/createUser.php $@; }
  edituser() { php batch/maintenance/edituser.php $@; }

else

  # ------------------------------------------------------------------
  # MariaDB container
  # ------------------------------------------------------------------

  # databases (cf. db name/user/pw in src/apps/koohii/config/app.yml)
  alias dbroot='mysql -u root -proot --default-character-set=utf8'
  alias dbgithub='mysql -u koohii -pkoohii --default-character-set=utf8 db_github'

fi

if isApacheService; then
  cd "$KK_APP"
else
  cd /var/lib/mysql/
fi
