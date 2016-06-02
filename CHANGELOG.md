# CHANGELOG for Ffuenf_Econda

This file is used to list changes made in each version of Ffuenf_Econda.

## 1.0.3 (June 2, 2016)

* fix proper variant tracking (only output configurables, if at least one simple is saleable; output only parent_id for configurables)
* add logging configuration
* remove stock_useparent as we always want to use the stock of simple products
* fix inclusion of product types
* add logging/exclude products without proper id

## 1.0.2 (March 17, 2016)

* add variant tracking by including simple and parent product ids properly
* add sku to econdas EAN field

## 1.0.1 (March 13, 2016)

* update travis / MageTestStand

## 1.0.0 (February 22, 2016)

* initial public release
