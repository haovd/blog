rsync -av --exclude=".*" --exclude="node_modules" ./ ~/Work/yme/svn/prevent-direct-access/trunk/
cd ~/Work/yme/svn/prevent-direct-access/trunk
svn add --force .
svn commit -m "bump new version"
