/**
 * @desc Помощник для работы с XMPP.
 * Использует Strophe.
 */
Ice.Model_Xmpp = Ice.Class.extend ({
	
	/**
	 * @desc Установка подключения
	 * @var params object Параметры
	 */
	__construct: function (params)
	{
		this.params = $.extend (
			{
				/**
				 * @desc Дебаг
				 * @var mixed
				 */
				log: 'fb',
				
				/**
				 * @desc Адрес сервиса
				 * @var string
				 */
				bosh_service: 'http://bosh.metajack.im:5280/xmpp-httpbind',
				
				username: "test",
				password: "test",
				
				onConnected: null,
				onDisconnected: null,
				onMessage: null
			},
			params
		);
		
		/**
		 * @desc Состояние соединения
		 * @var integer
		 */
		this.status = Strophe.Status.DISCONNECTED;
		
		/**
		 * @desc Колбеки
		 */	
		this.onConnectCallbacks = {};
		this.onConnectCallbacks [Strophe.Status.CONNECTING] = this.onConnectConnecting;
		this.onConnectCallbacks [Strophe.Status.CONNFAIL] = this.onConnectConnfail;
		this.onConnectCallbacks [Strophe.Status.DISCONNECTING] = this.onConnectDisconnecting;
		this.onConnectCallbacks [Strophe.Status.DISCONNECTED] = this.onConnectDisconnected;
		this.onConnectCallbacks [Strophe.Status.CONNECTED] = this.onConnectConnected;
		
		/**
		 * @desc Соединение
		 * @var Strophe.Connection
		 */
		this.connection = new Strophe.Connection (params.bosh_service);
		var a = this;
		this.connection.rawInput = function () { a.logInput.apply (a, arguments); };
		this.connection.rawOutput = function () { a.logOutput.apply (a, arguments); };
		
		this.parent ();
	},
	
	connect: function ()
	{
		var a = this;
		this.connection.connect (
			this.params.username,
			this.params.password,
			function () { a.onConnect.apply (a, arguments); }
		);
	},
	
	/**
	 * @desc Проверяет установленно ли подключение.
	 * @return boolean
	 */
	connected: function ()
	{
		return Boolean (this.connection) && 
			this.status == Strophe.Status.CONNECTED;
	},
	
	/**
	 * @desc Отключение
	 */
	disconnect: function ()
	{
		if (this.connection)
		{
			this.connection.disconnect ();
		}
	},
	
	/**
	 * @desc Вывод в лог
	 * @var data mixed
	 */
	log: function (data)
	{
		if (this.params.log == 'fb')
		{
			console.log ('Ice.Xmpp', data);
		}
	},
	
	logInput: function (data)
	{
		this.log ('RECV: ' + data);
	},
	
	logOutput: function (data)
	{
		this.log ('SENT: ' + data);
	},
	
	/**
	 * @desc 
	 * @var status integer Strophe.Status
	 */
	onConnect: function (status)
	{
		this.status = status;
		this.onConnectCallbacks [status].apply (this, arguments);
	},
	
	onConnectConnecting: function ()
	{
		this.log ('Strophe is connecting.');
		
	},
	
	onConnectConnfail: function ()
	{
		this.log ('Strophe failed to connect.');
		
	},
	
	onConnectDisconnecting: function ()
	{
		this.log ('Strophe is disconnecting.');
		
	},
	
	onConnectDisconnected: function ()
	{
		this.log ('Strophe is disconnected.');
		if (this.params.onDisconnected)
		{
			this.params.onDisconnected (this);
		}
	},
	
	onConnectConnected: function ()
	{
		this.log ('Strophe is connected.');
		
		a = this;
		this.connection.addHandler (
			function () { return a.onMessage.apply (a, arguments); },
			null, 'message', null, null,  null
		);
		this.connection.send ($pres ().tree ());
		
		if (this.params.onConnected)
		{
			this.params.onConnected (this);
		}
	},
	
	/**
	 * @var msg object
	 */
	onMessage: function (msg)
	{
		this.log ('Strophe incoming message.');
		
		// echo
//		var to = msg.getAttribute ('to');
//		var from = msg.getAttribute ('from');
//		var type = msg.getAttribute ('type');
//		var elems = msg.getElementsByTagName ('body');
//		if (type == "chat" && elems.length > 0)
//		{
//			var body = elems[0];
//
//			this.log (
//				'ECHOBOT: I got a message from ' + from + ': ' + 
//				Strophe.getText (body)
//			);
//
//			var reply = $msg ({to: from, from: to, type: 'chat'})
//				.cnode (Strophe.copyElement (body));
//			this.connection.send (reply.tree ());
//
//			this.log ('ECHOBOT: I sent ' + from + ': ' + Strophe.getText (body));
//		}
		
		if (this.params.onMessage)
		{
			this.params.onMessage (msg);
		}
		
		// we must return true to keep the handler alive.  
		// returning false would remove it after it finishes.
		return true;
	},
	
	/**
	 * @desc Отправляет сообщение.
	 * @param to string Получатель "admin@vipgeo.ru"
	 * @param message string Сообщение
	 */
	sendMessage: function (to, message)
	{
		var msg = $msg ({
			to: to,
			from: this.connection.jid,
			type: 'chat'
		})
			.c (
				'body',
				{
					ref: document.URL
				},
				message
			);
		
		this.connection.send (msg.tree ());
	}
	
});