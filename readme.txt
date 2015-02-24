=== Wordstress ===
Contributors: thesp0nge
Tags: security, penetration test, wapt, security assessment, security scan
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6AYKJFX87UFGW
Requires at least: 3.0.0
Tested up to: 4.1.0
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

wordstress is a whitebox security scanner for wordpress powered websites.

== Description ==

[wordstress](https://rubygems.org/gems/wordstress) is a whitebox
security scanner for wordpress powered websites.

Site owners don't want to spend time in reading complex blackbox security scan
reports trying to remove false positives. A useful security tool must give them
only vulnerabilities really affecting installed plugins or themes.

Let's assume, plugin `foobar_plugin` version 3.4.3 has a sever SQL Injection
vulnerability. In one of several wordpress powered website, you installed
version 3.2.1 version that **is not vulnerable**.

A blackbox security scanner will try to enumerate installed plugins but it
can't tell the exact installed version. So, using a blackbox approach you'll
have a suspectious SQL Injection vulnerability you must validate and mitigate.
Unfortunately, you will lose precious time to spot a false positive since your
plugin is safe.

With wordstress plugin, you'll give [the security
tool](https://rubygems.org/gems/wordstress) the exact `foobar_plugin` version
installed on the system, 3.2.1. The tool will scan the knowledge base and
report 0 vulnerabilities. You save time and you can be focused only on stuff
really need your attention.

Of course you may argue that giving on the Internet a place where all your
website third parties plugins and themes name with version is not a wise
decision. This is correct, that's why wordstress plugin has a place in the
configuration pane where you can save a randomic generated key.

You must pass the correct key value to wordstress ruby gem in order to perform
the whitebox scan. If you provide the wrong key or you won't provide a key at
all, the wordstress plugin will give no information as output and then no
whitebox scan will be possible.

You don't like the key? Just reload the page a couple of times since you're
comfortable about the generated entropy and then save the settings.

== Installation ==

To install wordstress we must do the following:

0. As a preliminary step you may want to install on your laptop (or somewhere)
   the wordstress ruby scanner. You need a working ruby environment, please ask
   your preferred search engine if you need instructions on how to setup ruby on
   your operating system. Installing wordstress security scanner is
   straightforwardly easy: `gem install wordstress`.
1. download wordstress.zip and unpack the content to your
  `/wp-content/plugins/` directory
2. activate the plugin through the 'Plugins' menu in WordPress
3. navigate the Settings->Wordstress admin page
4. every time you enter wordstress setting page, a new key is automagically
   generated, to increase entropy you may want to reload the page a couple of
   times. When you're comfortable with the generated key, press the "Save Changes"
   button.
   The virtual page is now available at the url http://youblogurl/wordstress?worstress-key=the_key
5. from the command line, use wordstress security scanner this way:
   worstress -u http://yourblogurl/wordstress -k the_key
6. enjoy results

== Frequently Asked Questions ==

= Do I need this? =
Well, the short answer is... **yes**. [Wordpress](https://wordpress.org) is a
huge and popular platform and there are tons of plugins released every day.
There are also dailiy released security issues affecting those tiny php scripts
that may have a huge impact on thousands of websites out there; even yours.

So, yes, you **do** need a scheduled security scan over your websites.
wordstress is here to give you just the security issues you really have to
mitigate, no false positives, no waste of time.

= How do I change the API Key? =
In order to change the API key, you have just to reload the wordstress plugin
settings page and save the changes.

= Why do I need this plugin? ==
Unlike [wpscan](http://wpscan.org/) or other blackbox security scanners,
[wordstress](https://rubygems.org/gems/wordstress) uses a whitebox approach
when scanning a wordpress powered website. The idea behind wordstress is to
have a 100% false positives free scan and in order to do this, we can't rely on
bruteforce or guessing to enumerate plugins or themes.

[wordstress](https://rubygems.org/gems/wordstress) is intended to be used by
sysadmin or people authorized to scan a site, so whitebox approach is the best
option we have. With the list of installed plugins and themes, their version
number and their active/inactive status,
[wordstress](https://rubygems.org/gems/wordstress) can give site owners the
exact status of the vulnerabilities they have to patch.

= Will wordstress harm my website? =
Not at all. [wordstress](https://rubygems.org/gems/wordstress) will get the
virtual page on your website and it will found there all the information needed
to give you a whitebox security scan. In future scanner versions there will be
support for robots.txt inspection, but at your site it will be just some HTTP
GETs.

= Can BAD guys access the virtual page content? =
No. You choose the key you in the setting page. The key is generated hashing
some information about your website, a couple of timestamps and a couple of
pseudo randomic number.
In order to guess the key, an attacker must bruteforce a 39 alphanumeric string
and it will take a **lot** of attempts.

Without the key, the virtual page shows empty content. No information is given
without the correct key.

== Changelog ==

= 0.6 =
* First public version

