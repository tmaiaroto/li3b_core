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
	private static $_packageConfig;
	
	/**
	 * A list of all packages (libraries) and their repository locations.
	 * 
	 * @var array
	 */
	private static $_packages;
	
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
	public static function install($packageName=null) {
		if(empty($packageName)) {
			echo "No package name provided." . PHP_EOL;
			exit();
		}
		
		// Get all of the packages from the repo ini files.
		// See if this package even exists.
		static::_collectPackages();
		var_export(static::$_packages); exit();
		if(!in_array($packageName, array_keys(static::$_packages))) {
			echo "No package found by that name." . PHP_EOL;
			exit();
		}
		
		$appConfig = Libraries::get(true);
		$appRoot = $appConfig['path'];
		$appWebroot = $appRoot . '/webroot';
		
		$command = 'clone ' . static::$_packages[$packageName] . ' libraries/' . $packageName;
		system("/usr/bin/env -i HOME={$appRoot} {$git} {$command} 2>&1");
		// Hey, this library may have submodules of its own...Get them.
		$packageRoot = $appRoot . '/libraries/' . $packageName;
		$command = 'submodule update --init --recursive';
		system("/usr/bin/env -i HOME={$appRoot} {$git} {$command} 2>&1");
		
		if(!file_exists($appRoot . '/libraries/' . $packageName)) {
			echo "Failed to retrieve the package/library." . PHP_EOL;
			exit();
		}
		
		Bootstrap::installDependencies($packageName);
		
		$coreConfig = Libraries::get('li3b_core');
		$packageWebroot = $appRoot . '/libraries/' . $packageName . '/webroot';
		$libraryAddFile = $appRoot . '/config/bootstrap/libraries/' . $packageName . '.php';
		$configOptions = isset(static::$_packageConfig['configuration']) ? static::$_packageConfig['configuration']:array();
		
		// Symlink the webroot if the configuration for li3b_core says to.
		if($coreConfig['symlinkAssets'] && file_exists($packageWebroot)) {
			system("(cd {$appWebroot} && ln -s {$packageWebroot} {$packageName})");
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
			echo $working ? "be working.":" NOT be working.";
			echo PHP_EOL;
			return;
		}
		
		if(file_exists($libraryAddFile)) {
			echo "Installation successful!" . PHP_EOL;
		} else {
			echo "Installation failed. Could not write the file which adds the library with Libraries::add(). You can try manually adding the library." . PHP_EOL;
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
	public static function installDependencies($packageName=null) {
		if(empty($packageName)) {
			echo "No package name provided." . PHP_EOL;
			exit();
		}
		
		// Get all of the packages from the repo ini files.
		// See if this package even exists.
		static::_collectPackages();
		if(!in_array($packageName, array_keys(static::$_packages))) {
			echo "No package found by that name." . PHP_EOL;
			exit();
		}
		
		static::_getPackageConfig($packageName);
		
		$appConfig = Libraries::get(true);
		$appRoot = $appConfig['path'];
		//$git = '/usr/bin/git';
		$git = 'git';
		
		echo "Getting the dependencies for this package...\n\n";
		if(static::$_packageConfig['dependencies']) {
			foreach(static::$_packageConfig['dependencies'] as $lib => $repo) {
				// If we did this, then we would need to include -f and that would add the plugins to the main repo.
				// We don't want that.
				// $command = 'submodule add ' . $repo . ' libraries/' . $lib;
				// system("/usr/bin/env -i HOME={$appRoot} {$git} {$command} 2>&1");
				// So instead, clone them.
				
				if(file_exists($appRoot . '/libraries/' . $lib)) {
					// TODO: Read .git/config files and check for this.
					echo "It seems that {$lib} already exists. Please ensure that is it compatible with or is:\n {$repo}\n";
				} else {
					$command = 'clone ' . $repo . ' libraries/' . $lib;
					system("/usr/bin/env -i HOME={$appRoot} {$git} {$command} 2>&1");
					// Hey, this library may have submodules of its own...Get them.
					$packageRoot = $appRoot . '/libraries/' . $packageName;
					$command = 'submodule update --init --recursive';
					system("/usr/bin/env -i HOME={$packageRoot} {$git} {$command} 2>&1");
				}
			}
			echo "\nAll dependencies have been installed.\n";
			echo PHP_EOL;
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
	public static function configurePackage($packageName=null) {
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
	public static function search($query=null) {
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
	private static function _getPackageConfig($packageName=null) {
		$appConfig = Libraries::get(true);
		$appRoot = $appConfig['path'];
		
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
			
			self::$_packageConfig = $packageConfig;
		}
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
	private static function _collectPackages() {
		$appConfig = Libraries::get(true);
		$appRoot = $appConfig['path'];
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
		
		static::$_packages = $packageList;
		
	}
	
}
?>