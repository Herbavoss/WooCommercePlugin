# WooCommercePlugin
> (Manual Installation instructions) 

**GRAFT WooCommerce Plugin** - allows WooCommerce stores to accept CryptoCurrency (Bitcoin, Ethereum and GRFT) payments.

## Prerequisites:
- Your online store based on WordPress (you have to do it yourself)
- SMTP Server credentials (you have to do it yourself)
- GRAFT Node instance (you have to do it with: https://github.com/graft-project/graft-ng/wiki/Alpha-RTA-Testnet-Install-&-Usage-Instruction)
> You don't need this step if you installed  it  for Exchange Broker
- Exchange Broker (you have to do it with:  https://github.com/graft-project/exchange-broker/blob/master/README.md)
- Payment Gateway (you have to do it with: https://github.com/graft-project/payment-gateway/blob/master/README.md )

## Installation

### 1. Install plugin:
1.1. Download Plugin for WooCommerce as .zip file from (pic.1):
https://github.com/graft-project/WooCommercePlugin

![2019-01-28_15-52-04](https://user-images.githubusercontent.com/45132833/51841063-6444e480-2316-11e9-92a2-886c08ef314d.png)

Pic.1

Press button **“Clone or download” (Pic.1,[1])**  and choose button **“Download ZIP” (Pic.1,[2])**. Save WooCommercePlugin.zip file to directory in your computer. 

1.2. Upload  plugin to Wordpress:

1.2.1.  Log into your WordPress dashboard

1.2.2. Go to  WordPress Dashboard.

1.2.3. Enter menu  _**Plugins -> Add New(Pic.2)**_:

![2019-01-25_14-07-51](https://user-images.githubusercontent.com/45132833/51767866-42194f80-20e7-11e9-88e1-731850fad9d3.png)

Pic.2

1.2.4. Navigate **_ADD Plugins -> Add Plugin(Pic.3,1)_**:

![2019-01-25_14-25-30](https://user-images.githubusercontent.com/45132833/51767868-42b1e600-20e7-11e9-8c8f-27f33bcb21a5.png)

Pic.3

1.3. Choose (Pic.4,[3]) the **WooCommercePlugin.zip** file and press button **“Install Now” (Pic.4,[4])**:

![2019-01-25_14-18-11](https://user-images.githubusercontent.com/45132833/51767867-42b1e600-20e7-11e9-98d8-8eb84b7fcb06.png)

Pic.4

### 2. Activate plugin
2.1. Press button **“Activate Plagin” (Pic.5,[1])**:

![2019-01-25_17-21-51](https://user-images.githubusercontent.com/45132833/51767875-434a7c80-20e7-11e9-955e-0d3b5095b846.png)

Pic.5

2.2. Plugin is activated if you can see screen(Pic.6,[1]):

![2019-01-25_14-43-37](https://user-images.githubusercontent.com/45132833/51767870-42b1e600-20e7-11e9-8a3d-b0d43d2027ab.png)

Pic.6

### 3. Configure API key and Secret Key:
3.1. Go to Payment Gateway terminal and Login as Merchant ().

3.2. Create a new online story in Payment Gateway 

3.3. Create new API key.

3.3.  Press button **“Detail”** on the record of new API Key and save **API key** and **Secret Key**.

### 4. Configure setting:
4.1 Go to menu **_WooCommerce > Settings (Pic.7)_**:

![2019-01-25_14-55-09](https://user-images.githubusercontent.com/45132833/51767871-42b1e600-20e7-11e9-8d30-4a1da628f37d.png)

Pic.7

4.2. Press bookmark **“Payments” (Pic.8)**:

![2019-01-25_17-43-38](https://user-images.githubusercontent.com/45132833/51767876-434a7c80-20e7-11e9-9222-566faa28f93a.png)

Pic.8

4.3. Select row **GRAFT (Pic.8,[1])** and press button **Manage (Pic.8,[2])** 

4.4. Enter API credentials (Pic.9): 

![2019-01-25_17-49-58](https://user-images.githubusercontent.com/45132833/51767877-43e31300-20e7-11e9-9ea3-0295bf5505fb.png)

Pic.9

where:

**API Key (Pic.9,[1])** - API key from PaymentGateway
**API Secret (Pic.9,[2])** - API Secret from PaymentGateway.

For saving information  enable the plugin and press button **“Save changes”(Pic.9,[3])**.

4.5. Go to menu **_WooCommerce > Settings (Pic.7)_** again, press bookmark **“Payment” (Pic.10)**:

![2019-01-25_15-22-35](https://user-images.githubusercontent.com/45132833/51767873-434a7c80-20e7-11e9-8bc8-1555814f1be0.png)

Pic.10

Plugin is installed successfully if you can see on the screen record (Pic.10,[1]) and on the button Manage you can see filled API credentials (Pic.9).
