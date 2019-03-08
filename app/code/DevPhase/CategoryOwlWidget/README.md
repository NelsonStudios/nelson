# magento2-category-owl-widget

#Features
<ul>
<li>Add Category List Any Where</li>
<li>Automatic Pick Default Store Category as Parent</li>
<li>Category Image into List</li>
<li>Can Manage Image Size</li>
<li>Can Assign Custom Parent Category</li>
</ul>

<h2>Composer Installation Instructions</h2>
Add GIT Repository to composer
<pre>
composer config repositories.emizentech-magento2-category-widget vcs https://github.com/emizentech/magento2-category-owl-widget/
</pre>

After that, need to install this module as follows:
<pre>
  composer require magento/magento-composer-installer
  composer require emizentech/categoryowlwidget
</pre>


<br/>
<h2> Mannual Installation Instructions</h2>
go to Magento2Project root dir 
create following Directory Structure :<br/>
<strong>/Magento2Project/app/code/DevPhase/CategoryOwlWidget</strong>
you can also create by following command:
<pre>
cd /Magento2Project
mkdir app/code/DevPhase
mkdir app/code/DevPhase/CategoryOwlWidget
</pre>



<h3> Enable DevPhase/CategoryOwlWidget Module</h3>
to Enable this module you need to follow these steps:

<ul>
<li>
<strong>Enable the Module</strong>
<pre>bin/magento module:enable DevPhase_CategoryOwlWidget</pre></li>
<li>
<strong>Run Upgrade Setup</strong>
<pre>bin/magento setup:upgrade</pre></li>
<li>
<strong>Re-Compile (in-case you have compilation enabled)</strong>
	<pre>bin/magento setup:di:compile</pre>
</li>
</ul>

