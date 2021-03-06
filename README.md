# mangoboilerplate
The mangoboilerplate is used with the Mango ICT - CLI to create and deploy mango websites to the mangoictcloud.com.

[![PHP Version][npm-image]][npm-url]
[![MGS Version][mgs-image]][mgs-url]
  
## Installation
To install and alter the boilerplate with all its rich features please use the Mango ICT - CLI called "mgs".
First install this through node:

```
npm install mgs -g
```

Create a new directory

```
mkdir testwebsite
cd testwebsite
```

Add a user to the directory

```
mgs addUser
```

Then start configuring a new website with the following command:

```
mgs init
```

To deploy to the mangoictcloud.com simply execute:

```
mgs push
```


## License

  [Apache 2.0](LICENSE)
  
[npm-image]: https://img.shields.io/badge/php-v5.3-brightgreen.svg
[npm-url]: https://www.mangoict.com/docs_mangoboilerplate
[mgs-image]: https://img.shields.io/badge/mgs-v1.0.4-brightgreen.svg
[mgs-url]: https://www.mangoict.com/docs_mgs
