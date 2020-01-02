#! /bin/bash
#! -*- coding:utf-8; mode:shell-script; -*-

set -euo pipefail

case $# in
    0)
        zipname='notenservice.zip'
        ;;
    1)
        zipname="$1"
        ;;
    *)
        echo "Usage: $0 [NAME]" >&2
        exit 1
esac

declare -A fonts

fonts['Ubuntu-Regular']='https://fonts.gstatic.com/s/ubuntu/v14/4iCs6KVjbNBYlgoKfw72.woff2'
fonts['Ubuntu-Italic']='https://fonts.gstatic.com/s/ubuntu/v14/4iCu6KVjbNBYlgoKej70l0k.woff2'
fonts['Ubuntu-Bold']='https://fonts.gstatic.com/s/ubuntu/v14/4iCv6KVjbNBYlgoCxCvjsGyN.woff2'
fonts['UbuntuMono-Regular']='https://fonts.gstatic.com/s/ubuntumono/v9/KFOjCneDtsqEr0keqCMhbCc6CsQ.woff2'

#fonts['Ubuntu-Regular']='https://fonts.gstatic.com/s/ubuntu/v14/4iCs6KVjbNBYlgoKcQ72j00.woff2'
#fonts['Ubuntu-Italic']='https://fonts.gstatic.com/s/ubuntu/v14/4iCu6KVjbNBYlgoKej76l0mwFg.woff2'
#fonts['Ubuntu-Bold']='https://fonts.gstatic.com/s/ubuntu/v14/4iCv6KVjbNBYlgoCxCvjvmyNL4U.woff2'
#fonts['UbuntuMono-Regular']='https://fonts.gstatic.com/s/ubuntumono/v9/KFOjCneDtsqEr0keqCMhbCc0CsTKlA.woff2'

for font in "${!fonts[@]}"
do
    url="${fonts[${font}]}"
    file="fonts/${font}.woff2"
    [ -e "${file}" ] || wget --no-verbose -O "${file}" "${url}"
done

rm -rf notenservice/
mkdir -p notenservice/data/ notenservice/fonts/
cp -t notenservice/ README.md LICENSE .htaccess index.php notenservice.php notenservice.css notenservice.js notenservice.json
cp -t notenservice/data/ data/.htaccess
cp -t notenservice/fonts/ fonts/{.htaccess,*.css,*.woff2}

zip -rT "${zipname}" notenservice/
