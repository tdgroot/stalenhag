This package provides a command line application to download Simon Stalenhag's latest art to your machine. 

I love Simon Stalenhag's art and wanted to have his art rotating through my desktop wallpaper collection. 
Therefore I created a command line application to download his art to a specific directory. 
It only downloads the files that you don't have yet.

### Installation

**The composer way**

- Run command `composer global require timpack/stalenhag`
- Make sure `~/.config/composer/vendor/bin` is in your `$PATH` variable

**The git way**
- Run command `git clone git@github.com:Desmaster/stalenhag.git`
- Then run the application using `bin/stalenhag` 

### Usage

-  `stalenhag -v --path="~/Pictures/Stalenhag/"`

##### Options

**-v** *(Optional)* Verbose mode. Use this option to enable stdout output.

**-f** *(Optional)* Force mode. Use this option if you feel like overwriting files that already exist.

**--path** *(Required)* Path. Specify directory path where the files should be placed. 

### Requirements

- php >= 5.6
- composer

### Developed using

- php >= 7.0
- linux kernel >= 4.4

### Credits

Credits go to Simon Stalenhag for his amazing work!

- [Red Bubble(buy prints)](http://www.redbubble.com/people/simonstalenhag)
- [Site](http://simonstalenhag.se/)
- [Twitter](https://twitter.com/simonstalenhag)
