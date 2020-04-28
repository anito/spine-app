#!/bin/bash
source ~/.bash_profile

#!/bin/bash
for i in "$@"
do
case $i in
    *)
    cmd="${i#*=}"
esac
done
hem "${cmd}"