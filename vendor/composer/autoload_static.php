<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita3f1c41c9fe0f8ac06c7145d329ea794
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Fragen\\GitHub_Updater\\Gist\\' => 27,
            'Fragen\\GitHub_Updater\\API\\' => 26,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Fragen\\GitHub_Updater\\Gist\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Fragen\\GitHub_Updater\\API\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/Gist',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita3f1c41c9fe0f8ac06c7145d329ea794::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita3f1c41c9fe0f8ac06c7145d329ea794::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInita3f1c41c9fe0f8ac06c7145d329ea794::$classMap;

        }, null, ClassLoader::class);
    }
}
