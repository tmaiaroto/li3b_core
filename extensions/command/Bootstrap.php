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
	
	private static $_packageConfig;

	/**
	 * Generates the appropriates indexes for the words collection.
	 *
	 * @param $packageName The name of the library/plugin to install.
	 * @return void
	*/
	public static function install($packageName=null) {
		if(empty($packageName)) {
			echo "No package name provided." . PHP_EOL;
			exit();
		}
		
		Bootstrap::installDependencies($packageName);
		
		$coreConfig = Libraries::get('li3b_core');
		$appConfig = Libraries::get(true);
		$appRoot = $appConfig['path'];
		$appWebroot = $appRoot . '/webroot';
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
	
	
}
?>