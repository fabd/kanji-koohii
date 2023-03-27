# --------------------------------------------------------------------
# Bash config shared between local containers AND production server.
#
#   The expected environment is a Debian/Ubuntu distro.
#
#   The application specific aliases should go to koohii_dev/prod.sh
#
# --------------------------------------------------------------------

export HISTFILESIZE=10000
export HISTSIZE=10000
export HISTCONTROL=ignoreboth

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

# --------------------------------------------------------------------
# Aliases
# --------------------------------------------------------------------

alias l='ls -ahl --color --group-directories-first'
alias ncdu='ncdu --color dark'

# --------------------------------------------------------------------
# Utilities
# --------------------------------------------------------------------

# IPv4
ip() {
  MYIP=$(curl -s ifconfig.me)
  #echo -n $MYIP | pbcopy     # (macOS)
  echo -n $MYIP | xsel -b # (Ubuntu)
  echo "IP Address copied to clipboard ($MYIP)"
}
