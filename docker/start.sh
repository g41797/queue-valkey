#!/usr/bin/env bash

#-------------------------
# ubuntu - github actions
# fedora - dev environment
#-------------------------

OSNAME=$(source /etc/os-release | echo $ID| grep fedora)

if [ -z $OSNAME ]
    then
        export PKMAN="apt"
fi

if [ -n $OSNAME ]
    then
        export PKMAN="dnf"
fi

echo
echo Install python+pip
echo
sudo $PKMAN install python3-pip

echo
echo Install LocalStack
echo
curl -Lo localstack-cli-3.4.0-linux-amd64-onefile.tar.gz \
    https://github.com/localstack/localstack-cli/releases/download/v3.4.0/localstack-cli-3.4.0-linux-amd64-onefile.tar.gz
sudo tar xvzf localstack-cli-3.4.0-linux-*-onefile.tar.gz -C /usr/local/bin
rm -f localstack-cli-3.4.0-linux-*-onefile.tar.gz
localstack --version

echo
echo Start LocalStack
echo
export LOCALSTACK_AUTH_TOKEN="ls-KESiVaLi-4697-7857-MEna-ziqIXeSaf962"
export LOCALSTACK_SERVICEES="sqs"
export LOCALSTACK_SQS_ENDPOINT_STRATEGY="path"
localstack start -d

echo
echo Uninstall aws utilities
echo
pip uninstall -y awscli-local
pip uninstall -y awscli

echo
echo Ininstall aws utilities
echo
curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
unzip -qq -u awscliv2.zip
sudo ./aws/install --bin-dir /usr/local/bin --install-dir /usr/local/aws-cli --update
which aws
ls -l /usr/local/bin/aws
aws --version
rm -f awscliv2.zip
pip install awscli-local
pip install awscli

echo
echo Check SQS service and aws cli
echo
pwd
echo Create Queue
awslocal sqs create-queue --queue-name test-queue.fifo --attributes FifoQueue=true,ContentBasedDeduplication=true
echo Delete Queue
awslocal sqs delete-queue --queue-url "http://localhost.localstack.cloud:4566/queue/us-east-1/000000000000/test-queue.fifo"

date
pwd

