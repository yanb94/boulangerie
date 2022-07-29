<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0b78bbd9add662ffab07c6872b7f68c0
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'Grav\\Plugin\\ResizeImg\\' => 22,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Grav\\Plugin\\ResizeImg\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Grav\\Plugin\\ResizeImgPlugin' => __DIR__ . '/../..' . '/resize-img.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0b78bbd9add662ffab07c6872b7f68c0::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0b78bbd9add662ffab07c6872b7f68c0::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit0b78bbd9add662ffab07c6872b7f68c0::$classMap;

        }, null, ClassLoader::class);
    }
}