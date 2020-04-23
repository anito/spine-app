#!/bin/bash
source ~/.bash_profile

#!/bin/bash
for i in "$@"
do
case $i in
    *)
    cmd="${i#*=}"
    shift # past argument=value
    ;;
    --default)
    DEFAULT=YES
    shift # past argument with no value
    ;;
    *)
            # unknown option
    ;;
esac
done
hem "${cmd}"