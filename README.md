# Torch
Admin generator for CodeIgniter v4

# Installation
```
composer require waga/torch
```

> **Note**: Need to call Torch\ComposerScripts::postUpdate in order to finish installation. 
Could be done using composer.json - scripts attribute:
```
"scripts": {
    "torch-post-update": "Torch\\ComposerScripts::postUpdate",
    "post-update-cmd": [
        "@composer dump-autoload",
        "CodeIgniter\\ComposerScripts::postUpdate",
        "@torch-post-update -- --app-folder=app"
    ]
}
```
