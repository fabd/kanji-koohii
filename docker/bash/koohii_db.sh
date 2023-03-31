# ====================================================================
# Aliases & utils for the Mariadb container
#
#   backup-db <db_name>
#
#     Create a local backup, available in ./docker/initdb.d/ on the host.
#     Optional database name, defaults to the sample github database.
#
# ====================================================================

KO_DB_USER='root'
KO_DB_PASS='root'
KO_DB_CHAR='--default-character-set=utf8'

backup-db() {
  local DB_NAME=${1:-db_github}

  [[ -z "$1" ]] && echo -e "\n  \033[30;43m Note! \033[0m  Database name not provided - using 'db_github'\n"

  local DEST='/docker-entrypoint-initdb.d'
  local FILENAME=${DEST}/$(/bin/date +\%F)-${DB_NAME}.sql.gz

  # replace backup if it already exists
  [[ -e "$FILENAME" ]] && rm "$FILENAME"

  echo -e "  ... running mysqldump & archiving"

  mysqldump --opt -u$KO_DB_USER -p$KO_DB_PASS $KO_DB_CHAR $DB_NAME | gzip -v1 - >"$FILENAME"

  echo -e "  ... testing archive"

  gunzip -t "$FILENAME"

  [[ $? -eq 0 ]] && echo -e "\n  \e[30;42m ARCHIVED \e[0m  ${FILENAME} \n"
  [[ $? -ne 0 ]] && echo -e "\n  \e[30;43m ERROR \e[0m ... \e[0m"
}

# --------------------------------------------------------------------
# aliases
# --------------------------------------------------------------------
alias dbroot="mysql -u$KO_DB_USER -p$KO_DB_PASS $KO_DB_CHAR"
alias dbgithub="mysql -u$KO_DB_USER -p$KO_DB_PASS -D db_github $KO_DB_CHAR"
