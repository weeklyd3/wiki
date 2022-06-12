echo Testing on port 3329
echo Deleting temporary folder if it exists...
rm -rf testing/
echo Making temporary folder...
mkdir testing
cp -R . testing/
rm -rf testing/testing
cd testing
php -S 0.0.0.0:3329