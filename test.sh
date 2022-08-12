echo Testing on port 3329
echo Deleting temporary folder if it exists...
rm -rf testing/
echo Making temporary folder...
mkdir testing
cp -R . testing/
rm -rf testing/testing
bash testAddNotices.sh
cd testing
echo Starting web server...
php -S 0.0.0.0:3329
