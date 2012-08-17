<?php

namespace li3b_core\extensions\command;

use lithium\core\Libraries;
use lithium\util\Set;
use lithium\util\Inflector;
use lithium\data\Connections;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use DirectoryIterator;

class Bootstrap extends \lithium\console\Command {
	
	/**
	 * Holds a package/library configuration.
	 * 
	 * @var array
	 */
	private $_packageConfig;
	
	/**
	 * A list of all packages (libraries) and their repository locations.
	 * 
	 * @var array
	 */
	private $_packages;
	
	private $_gitCommand;
	
	private $_appConfig;
	
	protected function _init() {
		// TODO: change this based on some configuration file somewhere...
		// Or look for the command in various places.
		$this->_gitCommand = 'git';
		
		$this->_appConfig = Libraries::get(true);
		$this->_appConfig['webroot'] = $this->_appConfig['path'] . '/webroot';
		
		$appRoot = $this->_appConfig['path'];
		system("/usr/bin/env -i HOME={$appRoot} > /dev/null 2>&1 &");
		
		parent::_init();
	}
	
	/**
	 *  Updates a single package/library or all of them.
	 * 
	 * @param $packageName The name of the library/plugin to update (or none if updating all)
	 * @return void
	 */
	public function update($packageName=null) {
		$appRoot = $this->_appConfig['path'];
		// These are submodules...If one is provided, just update all submodules.
		// Or if "submodules" is passed (this means there cannot be a library named "submodules).
		$submodules = array('li3b_core', 'lithium', 'li3_flash_message', 'submodules');
		if(in_array($packageName, $submodules)) {
			echo "Updating Lithium Bootstrap and other submodules...\n";
			// If using li3_boostrap, this will update the submodule commit number.
			$command = 'pull';
			system("{$this->_gitCommand} {$command} 2>&1");
			// Submodules
			$command = 'submodule update --recursive';
			system("{$this->_gitCommand} {$command} 2>&1");
			$this->clear();
			echo "Update complete!\n";
			exit();
		}
		
		$libraries = array();
		$filesAndDirectories = scandir($appRoot . '/libraries');
		$notToPull = array('.', '..', '.DS_Store', 'empty', 'li3b_core', 'li3_flash_message', 'lithium');
		foreach($filesAndDirectories as $entry) {
			if(!in_array($entry, $notToPull)) {
				$libraries[] = $entry;
			}
		}
		
		if(!empty($packageName) && !in_array($packageName, $libraries)) {
			echo "No package/library found by that name.\n";
			exit();
		}
		
		if(in_array($packageName, $libraries)) {
			$libraries = array($packageName);
		}
		
		// Update the packages/libraries.
		foreach($libraries as $library) {
			$command = 'pull';
			$libraryRoot = $appRoot . '/libraries/' . $library;
			system("(cd {$libraryRoot} && {$this->_gitCommand} {$command}) 2>&1");
			// Maybe it has some submodules of its own...
			$command = 'submodule update --recursive';
			system("(cd {$libraryRoot} && {$this->_gitCommand} {$command}) 2>&1");
			echo $this->nl(2);
			//$this->clear();
		}
		
		echo "Update complete!\n";
	}
	
	/**
	 * Installs a package/library from the Lithium Bootstrap repository.
	 *
	 * Note: Additional repository coinfiguration files can be added
	 * to the main application under the `_repos` directory.
	 * There is also a default, core list under the `li3b_core` library's
	 * `_repos` directory.
	 * 
	 * For now, all packages/libraries must be uniquely named.
	 * 
	 * @param $packageName The name of the library/plugin to install.
	 * @return void
	*/
	public function install($packageName=null) {
		if(empty($packageName)) {
			echo "No package name provided.\n";
			exit();
		}
		
		// Get all of the packages from the repo ini files.
		// See if this package even exists.
		$this->_collectPackages();
		if(!in_array($packageName, array_keys($this->_packages))) {
			echo "No package found by that name.\n";
			exit();
		}
		
		$appRoot = $this->_appConfig['path'];
		$appWebroot = $this->_appConfig['webroot'];
		$packageRoot = $appRoot . '/libraries/' . $packageName;
		if(!file_exists($packageRoot)) {
			echo "Installing...\n";
			$command = 'clone ' . $this->_packages[$packageName] . ' libraries/' . $packageName;
			system("{$this->_gitCommand} {$command} 2>&1");
			// Hey, this library may have submodules of its own...Get them.
			$command = 'submodule update --init --recursive';
			system("{$this->_gitCommand} {$command} 2>&1");
			$this->clear();
		} else {
			echo "This package appears to already have been installed.\n";
		}
		
		if(!file_exists($packageRoot)) {
			echo "Failed to retrieve the package/library.\n";
			exit();
		}
		
		Bootstrap::installDependencies($packageName);
		
		$coreConfig = Libraries::get('li3b_core');
		$packageWebroot = $appRoot . '/libraries/' . $packageName . '/webroot';
		$libraryAddFile = $appRoot . '/config/bootstrap/libraries/' . $packageName . '.php';
		$configOptions = isset($this->_packageConfig['configuration']) ? $this->_packageConfig['configuration']:array();
		
		// Symlink the webroot if the configuration for li3b_core says to.
		if($coreConfig['symlinkAssets'] && file_exists($packageWebroot)) {
			echo "Creating symlinks for assets...\n\n";
			system("(cd {$appWebroot} && ln -s {$packageWebroot} {$packageName})");
		}
		
		// Make sure the directory exists.
		if(!file_exists($appRoot . '/config/bootstrap/libraries')) {
			mkdir($appRoot . '/config/bootstrap/libraries', 0777, true);
		}
		
		if(!file_exists($libraryAddFile)) {
			$fp = fopen($libraryAddFile, 'x+');
			
			fwrite($fp, '<?php');
			fwrite($fp, "\n");
			fwrite($fp, 'use lithium\core\Libraries;');
			fwrite($fp, "\n");
			fwrite($fp, "\n");
			fwrite($fp, "Libraries::add('{$packageName}', ");
				fwrite($fp, var_export($configOptions, true));
			fwrite($fp, ");\n");
			fwrite($fp, '?>');
			
			fclose($fp);
		} else {
			$working = Libraries::get($packageName);
			echo "This library was already installed; it appears to ";
			echo $working ? "be working.":" NOT be working.\n";
			return;
		}
		
		if(file_exists($libraryAddFile)) {
			echo "Installation successful!\n";
		} else {
			echo "Installation failed. Could not write the file which adds the library with Libraries::add(). You can try manually adding the library.\n";
		}
	}
	
	/**
	 * Installs any dependencies for the package/library via git.
	 * 
	 * Each library built specifically for use with Lithium Bootstrap
	 * (also known as "package" or "plugin") may require other libraries
	 * in order to work. These dependencies may or may not be other Lithium
	 * Bootstrap packages. They could simply be any library that's available
	 * from a git repository. However, they must come from a git repo.
	 * 
	 * This method will clone all of the dependencies into the main app's
	 * libraries directory. It will skip over any that may already exist.
	 * Note: It is possible that, despite having a library already, the
	 * dependency shares a name with another library. This would create
	 * a conflict for now and would need to be manually addressed.
	 * 
	 * @param string $packageName The package/library name from a Lithium Bootstrap plugin repository.
	 */
	public function installDependencies($packageName=null) {
		if(empty($packageName)) {
			echo "No package name provided.\n";
			exit();
		}
		
		// See if this package even exists and while we're at it, get it's config.
		if(!$this->_getPackageConfig($packageName)) {
			echo "No package found by that name or it has no dependencies.\n";
			exit();
		}
		
		$appRoot = $this->_appConfig['path'];
		echo "Getting the dependencies for this package...\n\n";
		if($this->_packageConfig['dependencies']) {
			foreach($this->_packageConfig['dependencies'] as $lib => $repo) {
				// Clone libraries instead of adding as submodules.
				// This will prevent them from ending up in the main repository.
				// TODO: Think about a flag option for making everything a submodule...
				if(file_exists($appRoot . '/libraries/' . $lib)) {
					// TODO: Read .git/config files and check for this.
					echo "It seems that {$lib} already exists. Please ensure that is it compatible with or is:\n {$repo}\n";
				} else {
					$command = 'clone ' . $repo . ' libraries/' . $lib;
					system("{$this->_gitCommand} {$command} 2>&1");
					// Hey, this library may have submodules of its own...Get them.
					$packageRoot = $appRoot . '/libraries/' . $packageName;
					$command = 'submodule update --init --recursive';
					system("{$this->_gitCommand} {$command} 2>&1");
					$this->clear();
				}
			}
			echo "\nAll dependencies have been installed.\n\n";
		}
		
	}
	
	/**
	 * Configures a package/library for Lithium Bootstrap.
	 * 
	 * If a library is built specifically for Lithium Bootstrap, it can have
	 * a `config.ini` file bundled with it under its `config` directory.
	 * This confg file holds instructions for us in order to make additional
	 * configurations that may (or may not) be necessary before using it.
	 * 
	 * This is anything that can't be covered by simply cloning the library
	 * into the main application and getting any submodules. This also does
	 * not include dependency management. For installation of dependencies,
	 * see the `installDependencies` command.
	 * 
	 * @param string $packageName The package/library name from a Lithium Bootstrap plugin repository.
	 */
	public function configurePackage($packageName=null) {
		// TODO: move some code from install() down here.
		// This allows libraries to be configured again without going through
		// the entire installation process again (which has checks to not clone
		// if directories already exist, etc.) which makes more sense if a user
		// wants to "re" - configure the package.
		// This also may become a sort of wizard...
	}
	
	
	/**
	 * Searches the repositories for plugins.
	 * 
	 */
	public function search($query=null) {
	}
	
	/**
	 * Gets a package's configuration.
	 * 
	 * This let's us know about its dependencies and such
	 * so that they can be installed. This can only be
	 * ran once the package has been cloned. The config file
	 * is contained within the library/package's codebase.
	 * 
	 * @param string $packageName
	 * @return boolean
	 */
	private function _getPackageConfig($packageName=null) {
		$appRoot = $this->_appConfig['path'];
		$packageConfigFile = $appRoot . '/libraries/' . $packageName . '/config/config.ini';
		
		if(file_exists($packageConfigFile)) {
			$packageConfig = parse_ini_file($packageConfigFile, true);
			if(!$packageConfig) {
				return false;
			}
			
			$defaults = array(
				'dependencies' => false
			);
			$packageConfig += $defaults;
			
			$this->_packageConfig = $packageConfig;
			return true;
		}
		
		return false;
	}
	
	/**
	 * Loops all Lithium Bootstrap repository files and puts together
	 * a list of all packages and their repository locations.
	 * 
	 * Note: This will NOT include any additional information such as
	 * description and friendly names. This is simply used to get
	 * a package/plugin from a git repository.
	 * 
	 * Also note: Package names MUST be unique.
	 * It's a good idea, if you're making one, to name it something
	 * creative that you know will end up being unique. Otherwise,
	 * it may not be accessible.
	 * 
	 * TODO: Work on some sort of prefix convention for each repo ini
	 * that will avoid this issue. So that only each ini file must
	 * contain unique names for packages.
	 * 
	 */
	private function _collectPackages() {
		$appRoot = $this->_appConfig['path'];
		$appRepoPath = $appRoot . '/_repos';
		$coreRepoPath = $appRoot . '/libraries/li3b_core/_repos';
		
		$packageList = array();
		// Add packages from li3b_core.
		foreach(glob($coreRepoPath . '/*.ini') as $repoFile) {
			$packages = parse_ini_file($repoFile, true);
			foreach($packages as $package => $config) {
				if(isset($config['repo'])) {
					$packageList[$package] = $config['repo'];
				}
			}
		}
		
		// Add packages from any repos that the main application may have added.
		foreach(glob($appRepoPath . '/*.ini') as $repoFile) {
			$packages = parse_ini_file($repoFile, true);
			foreach($packages as $package => $config) {
				// If the repo key was set and if we don't already have this package.
				if(isset($config['repo']) && !in_array($package, array_keys($packageList))) {
					$packageList[$package] = $config['repo'];
				}
			}
		}
		
		$this->_packages = $packageList;
	}
	
	/**
	 * Sets up connections for all other libraries to use.
	 * TODO: Evolve this into a connection wizard.
	 */
	public function setupConnections() {
		$appRoot = $this->_appConfig['path'];
		
		// Make sure the directory exists.
		if(!file_exists($appRoot . '/config/bootstrap/connections')) {
			mkdir($appRoot . '/config/bootstrap/connections', 0777, true);
		}
		
		
	}

}
?>