=== Affinidi Login - Passwordless Authentication ===
Contributors: affinidi
Tags: authentication, passwordless, multi-factor, sso, ecommerce
Requires at least: 6.4
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.1.2
License: MIT
License URI: https://github.com/affinidi/wordpress-affinidi-login/blob/main/LICENSE

AUGMENT EXPERIENCES WITH A SAFER, SIMPLER AND MORE PRIVATE WAY TO LOGIN

== Description ==

A paradigm shift in the registration and sign-in process, Affinidi Login is a game-changing solution for developers. With our revolutionary passwordless authentication solution your user's first sign-in doubles as their registration, and all the necessary data for onboarding can be requested during this streamlined sign-in/signup process. End users are in full control, ensuring that they consent to the information shared in a transparent and user-friendly manner. This streamlined approach empowers developers to create efficient user experiences with data integrity, enhanced security and privacy, and ensures compatibility with industry standards.

= Passwordless Authentication =

Offers a secure and user-friendly alternative to traditional password-based authentication by eliminating passwords and thus removing the vulnerability to password-related attacks such as phishing and credential stuffing.

= Decentralised Identity Management =

Leverages OID4VP to enable users to control their data and digital identity, selectively share their credentials and authenticate themselves across multiple platforms and devices without relying on a centralised identity provider.

= Uses Latest Standards =

Utilises OID4VP to enhance security of the authentication process by verifying user authenticity without the need for direct communication with the provider, reducing risk of tampering and ensuring data integrity.

= Features =

- Passwordless login experience for users using Affinidi Login and Affinidi Vault.

- Supports WooCommerce: Passwordless login, Seamless Customer Onboarding, and Customer profile creation.

= Shortcode =

You can use the Affinidi Login as a shortcode in your editor. Just add the following to display the button in the page:
    
    [affinidi_login]

= More References and Resources =

1. List the available data points in Affinidi Vault [here](https://docs.affinidi.com/docs/affinidi-vault/affinidi-vault-data/#user-profile-individual-data-points).
2. Requesting User Data from Affinidi Vault [here](https://docs.affinidi.com/docs/affinidi-vault/requesting-user-data/).
3. Restrict User Login for your Application [here](https://docs.affinidi.com/docs/use-cases/restrict-user-login/).
4. For Information on the latest updates and improvements to the Affinidi Trust Network, refer to [Changelog](https://docs.affinidi.com/changelog/) page.
5. Learn more about the plugin [here](https://docs.affinidi.com/labs/3rd-party-plugins/passwordless-authentication-for-wordpress/).

= Troubleshooting =

**Common Errors**

Refer list of [common issues, misconfigurations, and their resolution](https://docs.affinidi.com/other-resources/resolving-common-issues/) to help you get up and running quickly

If you encounter any other issues during the integration, please connect us filling out the [Contact Us](https://www.affinidi.com/get-in-touch). One of us will take a look on the Issue.

== Installation ==

1. Upload to the /wp-content/plugins/ directory
2. Activate the plugin
3. Visit Settings > Affinidi Login and follow the steps to configure Affinidi Login

= Set up Affinidi Login =

To configure the integration with Affinidi Login, Create Login Configuration using [Affinidi CLI](https://docs.affinidi.com/dev-tools/affinidi-cli/manage-login/#affinidi-login-create-config) or [Affinidi Developer Portal](https://docs.affinidi.com/dev-tools/affinidi-portal/#create-a-login-configuration)

= How Affinidi Login Works =

To learn more how Affinidi Login Works, visit [this page](https://docs.affinidi.com/docs/affinidi-login/how-affinidi-login-works/) for more information.


== Frequently Asked Questions ==

= What is Affinidi Login? =

Affinidi Login simplifies and secures login processes with passwordless authentication using OID4VP, empowering users with data control and privacy. It offers flexible integration options and simplified development to save time and resources, making it a game-changing solution for developers. Visit [this page](https://www.affinidi.com/product/affinidi-login) to learn more.

= What is a Login Configuration, and why do I need it? =

A Login Configuration is a setup that allows you to integrate Affinidi Login into your application, enabling a passwordless login experience. It contains essential information such as the name of your configuration, redirect URIs, and client credentials. Visit [this page](https://docs.affinidi.com/docs/affinidi-login/login-configuration/) to learn more.

= How do I begin implementing Affinidi Login? =

To get started, use the [Affinidi CLI](https://github.com/affinidi/affinidi-cli) or [Affinidi Portal](https://portal.affinidi.com/). This toolset enables developers to create, customise, and manage login configurations, making it easier to integrate Affinidi Login into your applications. For more details, explore our guide on [Login Configuration](https://docs.affinidi.com/docs/affinidi-login/).

= How can I reach out for further support? =

We are here to help. Please [Contact Us](https://www.affinidi.com/get-in-touch) and relevant personnel will reach out to assist you further.

== Changelog ==

Visit our [GitHub Releases](https://github.com/affinidi/wordpress-affinidi-login/releases) for the complete list of changes and releases.