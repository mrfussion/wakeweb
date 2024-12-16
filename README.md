# Wakeweb: Wake-on-LAN Web Interface

Wakeweb is a simple and user-friendly web interface that allows you to remotely wake up your computers using the Wake-on-LAN (WOL) protocol. The interface provides a secure way to manage multiple computers, select which one to wake, and verify the user's password before sending the WOL packet.

## Features

- **Select computer**: Choose from a list of configured computers to wake up.
- **Password Protection**: Secure the WOL action by requiring a password.
- **Online/Offline Status**: Displays the current status of the selected computer (online or offline).
- **Dark/Light Mode**: Toggle between dark and light themes for the interface.
- **Responsive Design**: Works on any device with a modern browser.
- **Easy Configuration**: Easily add new computers to the configuration by modifying a simple `config.php` file.
- **API Access**: Allows interacting with the WOL system via REST API to wake up or check the status of computers.

## Screenshots

<div style="display: flex; justify-content: space-around; align-items: center;">
  <img src="https://github.com/user-attachments/assets/421c453a-1c98-45cb-8c85-9b7a272c57ac" alt="Dark" width="400">
  <img src="https://github.com/user-attachments/assets/eba32439-5573-4cd1-9f45-520c664a0a4e" alt="Light" width="400">
</div>

## Requirements

- PHP 7.0 or higher
- Wake-on-LAN software (`wakeonlan` installed on the server)
- Basic knowledge of PHP and web servers for setting up

## Installation

### 1. Clone the repository:

```bash
git clone https://github.com/mrfussion/wakeweb.git
```

### 2. Rename and configure `config.php`:
#### 1. **Rename the configuration file**:
In the directory, you'll find a file called `config.php.template`. Rename it to `config.php`:

```bash
cd wakeweb
mv config.php.template config.php
```

#### 2. **Configure your computers**:
Open the `config.php` file and modify the values for each computer (e.g., the computer named `'medina'`). Make sure to set the MAC address, IP address, ping port, and computer name.

#### 3. **Generate the password hash**:
To secure the passwords, they should be stored in a hashed format. To generate a secure password hash, run the following command in your console:

```bash
php -r 'echo "Enter password: "; $p = trim(fgets(STDIN)); echo password_hash($p, PASSWORD_BCRYPT) . PHP_EOL;'
```

After entering the password, the command will return a hash similar to this:

```bash
$2y$10$Oty.zxL4C.UsqXH/2ieznOuseiFonp6AgTc75NnhDUbzLaXq5kOJ6
```

Replace the password in `config.php` with this hashed value in the corresponding section of the computer.

#### 4. **Add more computers**:
To add more computers, simply copy and paste the block for `'medina'` and replace the corresponding values with the new computers's information.

Example:

```php
'new_pc' => [
    'mac' => '00:11:22:33:44:55',
    'host' => '192.168.1.100',
    'pingPort' => 22,
    'password' => 'hashed-password',
    'pcName' => 'New computer',
],
```

### 3. Set up your web server:
Ensure that your web server (e.g., Apache, Nginx) is properly configured to serve PHP files.

### 4. Test the setup:
Visit the webpage in your browser, select a computer, enter the password, and click the "Wake up" button to send the WOL packet.

## Usage

### Web Interface

1. **Select a Computer**: Choose the computer you want to wake up from the dropdown list.
2. **Enter Password**: Provide the correct password for the selected machine.
3. **Click "Wake Up"**: If the computer is offline, the WOL packet will be sent, and the computer should turn on.
4. **Toggle Theme**: Switch between dark and light modes for the interface.

### API Usage

You can interact with the Wakeweb system programmatically via the following API endpoints.

#### Endpoints

1. **Wake Up a Computer**
	- **URL**: `/api?action=wake&computer={computer_id}`
   - **Method**: `GET`
   - **Parameters**:
     - `action`: The action to perform. Use `wake` to send a WOL packet.
     - `computer`: The identifier of the computer to wake up (based on the configuration in `config.php`).
   - **Response**:
     - `status`: "Sended" if the WOL packet was successfully sent, "Failed" if there was an issue.

	Example:
	
	```bash
	GET /api?action=wake&computer=computer1
	Response: {"status":"Sended"}
	```

2. **Check the Status of a Computer**
	- **URL**: `/api?action=status&computer={computer_id}`
	- **Method**: `GET`
	- **Parameters**:
		- `action`: The action to perform. Use `status` to check the current state of the computer (online/offline).
		- `computer`: The identifier of the computer to check (based on the configuration in `config.php`).
	- **Response**:
		- `status`: "online" if the computer is reachable, "offline" if not.

	Example:

	```bash
	GET /api?action=status&computer=computer1
	Response: {"status":"online"}
	```

#### Authentication
To secure the actions, a password is required for each WOL action. The password is stored securely in the `config.php` file for each computer. The user can pass the correct password via the web interface or via the API for validation.

- **Example of API request with authentication:**

	- When calling the wake action via the API, you must ensure the correct password is used (either passed as a parameter or checked in the back-end).

- **Error Handling**

	- Invalid action or missing parameters: If an invalid action or missing parameters are detected, the API will return an error message.

	  Example:

	  ```json
	  {"error": "Invalid action or missing parameters"}
	  ```

	- Computer not found: If the computer identifier is not found in the configuration, the API will return an error message.

	  Example:

	  ```json
	  {"error": "computer not found"}
	  ```

## Contributing
We welcome contributions to improve Wakeweb! If you'd like to contribute, please fork the repository, make your changes, and submit a pull request.

## Bug Reports
If you encounter any issues, please open an issue on the GitHub repository.

## License
This project is licensed under the Apache License 2.0 - see the LICENSE file for details.

## Acknowledgments
- Wake-on-LAN protocol for remote computer management.
- Bootstrap 5 for the frontend design and responsiveness.
- PHP for the server-side scripting.
