## How to setup auto-completion in your IDE


Probably the cleanest way to add stubs to your IDEA is to clone the repository and add the directory to the global path.
There are various ways to do this (Eclipse path in eclipse, global path in netbeans, multiple project roots in phpstorm etc).
I personally  prefer to put the files in my var folder along with my cache and log files. I don't like global settings 
as versions may vary. Also, I don't like to load a lot of stuff in memory I don't need in other projects.  
And heck, I may even edit the stub files when I discover a bug (and planning to submit a pull request later). 
Either way, feel free to load the stubs anyway you see fit. 

### Clone the repository to get the code
```
git clone https://github.com/ice/ide.git
```



