# **JJMVC**

## **Introduction**

Welcome to JJMVC! This is MVC that i have created
for everyone to use. I decided to create this tool for myself
but now i want to share it with you all! Let's make this
great tool together!


  - [Plugins](#plugins) 
    - [Core plugins](#core-plugins)
    - [Plugin structure](#Plugin-structure)
    - [Plugin request structure](#Plugin-request-structure)
    - [What about composer](#What-about-composer)
    - [How to create plugins](#How-to-create-plugins)
    - [Plugin aliases](#Plugin-aliases)
  - [Request path](#Request-path)
  - [File structure](#File-structure)
    - [/app](#/app)
    - [/logs](#/logs)
    - [/public](#/public)
  - [Language](#Language)
  - [Template loader](#Template-loader)
    - [How to create template](#How-to-create-template)
  - [Models](#Models)




## **Plugins**

JJMVC has its own plugin loader that handles loading of plugins. Plugins
can be loaded in every part of request. Plugins can be loaded in other plugins too if needed!
    
Plugin loader is lightweight and simple to use.

    plugin::load('plugin_name1, plugin_name2, etc...');

### **Core plugins**

Core plugins are plugins that are made for basic functioning of JJMVC. Core plugins are stored at:

    /app/plugins/data/core
        
Every core plugin uses namespace that defines that it is part of the core.
        
### **Plugin structure**
    
When developer creates new plugins it is important to know how to create new plugin properly.
        
This folder is for storing plugin logic.

    /app/plugins/data

If plugin requires any additional data (ex. config files) to be stored it should be stored here:
        
    /app/plugins/json  

This file contains information about plugins. When plugins are loaded, plugin loader checks information from this specific file:

    /app/plugins/plugins.json

In this file developer can create aliases for plugins. Aliases make it easier to load plugins.

    /app/plugins/plugin_aliases.json
                                    
### **Plugin request structure**
    
    plugin::load('
        maker:name:version,
        maker:name:version
    ');
        
    -> Loader opens plugins.json -file and checks if plugin exists 
    -> Pass file path to loader
    -> request plugin if it is not already requested
        
### **What about composer**

Composer folder

    /app/plugins/composer


User is able to load composer simply by calling "require COMPOSER" which then loads composer when needed.

If developer feels like loading composer on every request developer can include/require it at "/app/controllers/controller.php" or at "/public/index.php"
 
The reason why composer is supposed to load manually is that it loads less files that are not necessary. For necessary stuff JJMVC has it's own plugin loader which resembles composer but it is meant for manual loading. 
        
### **How to create plugins**

At the moment developer can add new plugins only manually but in future JJMVC does have feature that makes adding new plugins easier.
        
Required information:
- Author
- Plugin name
- Plugin version
        
Not required but recommended:
- Requirements
- PHP version
- Other plugins
        
In future with this information developer is able to upload plugin to jjmvc.net or to other 
unofficial plugin libraries and it is easily accessible to other developers. 
        
Data where all plugins are listed can be found in:

    /app/plugins/plugins.json

This is what plugin JSON should look like:
        
    {
        "author": {
            "pluginname": {
                "1.0": {
                    "file": "index.php",
                }
            }
        }
    }

And then how to load plugins:

    plugin::load('
        author:pluginname:1.0,
        otherauthor:pluginname:1.2
    ');
        
It is recommended to load plugins this way. It makes syntax easier to read.
        
Developers can use same names for plugins. Maker of the plugin and version numbering makes every plugin unique.
Same developer cannot create two plugins with same name. Instead developers should create new version of their plugin.

PLUGIN VERSION NUMBERING

There are multiple ways to implement version numbering.
        
**EXAMPLE**
- "a1.0"
- "b1.0"
- "new_plugin1.0"

It is still recommended to use basic version numbering to make things easier. 
Developers don't like to load things like "author:pluginname:new_plugin1.0" because the loader has its own difficult syntax already.
        
RECOMMENDED VERSION NUMBERING
        
- "1.0"
- "a1.0"
- "b1.0"
- "1.0.0"
- "a1.0.0"
- "b1.0.0"

"a" indicates alpha and "b" indicates "beta"
        
### **PLUGIN ALIASES**

Developer can set aliases for plugins. Aliases can be found here:

    /app/plugins/plugin_aliases.json

Aliases can be useful for plugins that are used often for example core plugins. Some core plugins have aliases by default.
Aliases are enabled by default. If developer wants
to disable aliases it is possible to set 2nd argument of Plugin Load to false. Aliases are simple to set.

**EXAMPLE**

    "alias_name": "core:view:1.0"
        
After that developer is able to load plugin as "alias_name"

**EXAMPLE**

    plugin::load('alias_name');
        
Aliases and normal syntax can used at the same time.

**EXAMPLE**
        
    plugin::load('
        alias_name,
        author:pluginname:1.0
    ');
        
## **Request path**

Request path is made simple. Every request goes through /public/index.php
and it redirects request data to /app/Controller.php that redirects data straight
to controller that URI is connected to. 

/public/index.php -> /app/Controller.php -> /app/controller/[URI_PATH]/index.php

URI path does not include $_GET data in URL so you are free to use $_GET in URL. 

For now prettyURL is not possible and it would require huge changes. How ever
in the future this feature will be included.
    
## **File structure**

### **/app**

App folder contains whole software logic in. Reason why software is built to App folder is that user does not have access to any critical part of the software. It makes this software one step more secure.

### **/logs**

Webserver logs come here

### **/public**
Webservers main folder that is accessible by user. User can use every resource in Public folder unless restricted by developer.
    
## **Language**

Developer can load langauge translations from 2 different sources.

JSON
    Developer should use JSON mostly for dynamic text translations.
    It is not useful in long run to use JSON for storing big chunks of text
    so avoid doing that. JSON file is loaded and set to "LANG" constant. Language is
    set automatically by reading "lang" cookie or by default setting (fi-fi).
    
DATABASE
    Developer should use database for big text and text that possibly requires
    changes to it. In most cases text is loaded from database.
        
## **Template loader**

W.I.P ( WORK IN PROGRESS )
Developer can create templates that can be loaded easily with custom data
from database. For now this feature is not fully designed.

### **FOLDER**
This folder is used for storing templates.

    /app/views/templates
        
### **How to create template**

    <?php
        namespace Core\App\Template;
        class exampletemplate extends \Core\App\Template {
        protected $template;
            
            public function __construct($values) {
                $this->template = '
                    <div class="section-12">
                        <h1 class="bigtext">' . $values['title'] . '</h1>
                        <p>' . $values['description'] .'</p>
                    </div>';
            }
        }

    ?>
        
Here is our example template. Class name of template should be same as filename.
This way we can easily call for file name and class at the same time in
template loader.

## **Models**

Models give developer easy way to control flow of data to database. Models are very easy to create when using JJCLI commandline commands.


        
        

