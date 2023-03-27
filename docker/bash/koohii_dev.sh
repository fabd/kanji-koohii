# ====================================================================
# Koohii aliases shared between dev & prod
# ====================================================================

if [[ -z "${KK_APP-}" ]]; then
  echo " Environment var KK_APP should be set in the parent script!"
  exit
fi

alias kk="cd $KK_APP"
alias kkweb="cd $KK_WEB"

# --------------------------------------------------------------------
# npm & other DEV tools
# --------------------------------------------------------------------

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

# --------------------------------------------------------------------
# Symfony 
# --------------------------------------------------------------------

# symfony CLI
alias sf='./symfony'

# clear the config only (not the study pages etc!)
alias sf-cache-clear-config='sf cache:clear --type=config'

# website maintenance (locally testing the "website unavailable" page)
alias kkdis='sf project:disable koohii dev'
alias kkena='sf project:enable koohii dev'

# --------------------------------------------------------------------
# Configs & Logs
# --------------------------------------------------------------------

# configs
kkphpini() { vim /etc/php/7.4/apache2/conf.d/koohii.php.ini; }

# where the php error_log() output goes to
kkphplog() { tail -f /var/log/apache2/error.log; }

# custom log with colored output (see lib/ColorizedLogger.php)
kklog() { tail -f koohii-log.txt; }

# --------------------------------------------------------------------
# Tools
# --------------------------------------------------------------------

# test email sending
alias kkmail="php $KK_APP/batch/admin/mailtest.php"

# admin misc
createuser() { php batch/admin/createuser.php $@; }
edituser() { php batch/admin/edituser.php $@; }
