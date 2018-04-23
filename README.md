
## Example code for solarwinds-soap

### Installation
```bash
composer update
```

### Setup
```bash
cp config.yml.example config.yml
vim config.yml
```

### Command execution
```bash
./ncentral.php
```

### Retrieve a list of customers.
```bash
./ncentral.php customer:list
```

### Retrieve a list of devices for a customer.
```bash
./ncentral.php device:list [customerid]
```

### Retrieve some basic information for a device.
```bash
./ncentral.php device:get [deviceid]
```

### Retrieve the custom fields for a device.
```bash
./ncentral.php device:property:list [deviceid]
```