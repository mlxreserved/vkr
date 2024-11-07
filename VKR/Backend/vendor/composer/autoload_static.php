<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6aebd7b1556baff99b46095fe243ad97
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
        'C' => 
        array (
            'Cupitman\\Backend\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
        'Cupitman\\Backend\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6aebd7b1556baff99b46095fe243ad97::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6aebd7b1556baff99b46095fe243ad97::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit6aebd7b1556baff99b46095fe243ad97::$classMap;

        }, null, ClassLoader::class);
    }
}
