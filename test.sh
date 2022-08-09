echo Testing on port 3329
echo Deleting temporary folder if it exists...
rm -rf testing/
echo Making temporary folder...
mkdir testing
cp -R . testing/
rm -rf testing/testing
echo Adding notices...
echo "<div style=\"color: red; font-weight: 700;\">" >> testing/footer.html
echo -n "Secret test run (<abbr title=\"" >> testing/footer.html
git rev-parse HEAD &>> testing/footer.html
truncate -s -1 testing/footer.html
echo -n "\">" >> testing/footer.html;
git rev-parse --short HEAD &>> testing/footer.html
truncate -s -1 testing/footer.html
echo -n "</abbr>" >> testing/footer.html
echo "): New features may or may not make it into production." >> testing/footer.html
echo "This version may have new features or bugs," >> testing/footer.html
echo "and it may significantly differ from the current version." >> testing/footer.html
echo "</div>" >> testing/footer.html
cd testing
echo Starting web server...
php -S 0.0.0.0:3329
