#!/bin/bash
source ~/.bash_profile

if [ $# -gt 0 ]; then
    for i in "$@"
        do
        case $i in
            *)
            cmd+="${i#*=} "
        esac
    done
    hem ${cmd}

else
    echo "Hem needs at least 1 argument"
    hem
fi