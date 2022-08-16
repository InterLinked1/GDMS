# GDMS

**PHP SDK for the Grandstream Device Management System API**

## Background

This is a simple SDK for using the GDMS API (Grandstream Device Management System API).

The Grandstream Device Management System is a service provided by Grandstream Networks, Inc. that allows VoIP administrators to manage connected VoIP devices, such as HT 8xx series ATAs (analog telephone adapters). The functionality provided by GDMS is quite nice and adds a lot of value to the Grandstream hardware offering.

Grandstream provides an [API](https://doc.grandstream.dev/GDMS-API/EN/) for GDMS which developers can use to automate interfacing with the GDMS. Unfortunately, the API is quite cryptic and anything but intuitive to use, and their documentation is rather confusing and leaves a lot to be desired. For starters:

- Multiple sets of keys (secrets, passwords) are required to use the API
- An access token needs to be obtained before the API itself can be used, for each session
- Some of the request formats are inconsistent
- Certain parts of requests are hashed, sometimes multiple times
- Each request needs to have a calculated signature, calculated in a special way

In other words, it's probably among the most arcane APIs that any developer will encounter today. The complexity of the GDMS API has made it difficult for businesses and organizations interested in using the GDMS API to utilize it and benefit from the integration and capabilities provided by GDMS.

Fear not, however! There is a solution: for the past few months, with support from Grandstream themselves, we have been able to develop a simple SDK that can be used to interface with the GDMS API. This takes the pain out of using the GDMS API so that you can focus on your application instead, not the confusing interface of the GDMS API.

Now, you too can get started with using the GDMS API in just a few simple steps.

Note that this project is not affiliated in any way with Grandstream Networks, Inc.

## Usage

Using the GDMS SDK is very simple. All you need to do is download `GDMS.php` from this repo and include it in your project, like so:

``require('GDMS.php');``

From there, you can create a new `GDMS` class and make API calls using the SDK interface.

Generally, responses returned from SDK functions are not modified in any way, so they reflect responses as documented in the GDMS API documentation. The philosophy of this SDK is to make it easy to make GDMS requests and then to let the user deal with the responses.

For example usage, please take a look at `SampleGDMSClient.php` to see how this SDK can be used.

## Contributions & Bugs

Currently, many of the most common operations are included, but not everything exists in the SDK yet. Contributions to add missing operations are welcome!

If you find a bug with this SDK, please report a new issue.
