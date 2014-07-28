var Controller_Chat = Class.extend ({
	$agence_id: null,
	$message: null,
	$code: null,
	$join_id: null,
	$last_id: null,
	
	joinChat: function (params)
	{
		params.back = { $sender: this };
		
		Controller.call (
			'Chat_Session/join',
			params,
			this.joinChatCallback,
			true
		);
	},
	getLastId: function (cont)
	{
		return 0;
	},
	getLastMessages: function (cont)
	{
		this.$last_id = this.getLastId (cont);
		
		if (this.$last_id > 0)
		{
			this.receiveLast (
				this.$join_id, 
				this.$last_id
			);
		}
	},
	appendMessage: function (cont, obj)
	{

	},
	indexChatCallback: function (result)
	{
		if (typeof result.data.code != 'undefined')
		{
			var $this = result.back.$sender;
			
			$this.$join_id = result.data.join_id;
			
			$this.$name	 = result.data.name;
			$this.$code  = result.data.code;
			
			$this.joinChat ({
				code: $this.$code,
				name: $this.$name
			});
			
			return;
		}
	},
	indexChat: function (params)
	{
		if (typeof params.route == 'undefined')
		{
			return;
		}
		
		params.back = { $sender: params.sender };
		
		Controller.call (
			params.route,
			params,
			this.indexChatCallback,
			true
		);
	},
	createChatCallback: function (result)
	{
		var $this = result.back.$sender;
		
		$this.$code = result.data.code;
		
		$this.joinChat ({
			code: $this.$code,
			name: $this.$name
		}); 
	},
	createChat: function (params)
	{
		if (typeof params.route == 'undefined')
		{
			return;
		}
		
		params.back = { $sender: this };
		
		Controller.call ( 
			params.route,
			params,
			this.createChatCallback,
			true
		);
	},
	joinChatCallback: function (result)
	{
		var $this = result.back.$sender;
		
		$this.$join_id = result.data.join_id;
		$this.$name	 = result.data.name;
		$this.$code  = result.data.code;

		//$this.receiveAll ($this.$join_id);
	},
	receiveAll: function (join_id)
	{
		Controller.call (
			'Chat_Message/roll',
			{
				session_join_id: join_id,
				back: {
					$sender: this
				}
			},
			this.receiveCallback,
			true
		);
	},
	receiveLast: function (join_id, last_id)
	{
		Controller.call (
			'Chat_Message/last',
			{
				session_join_id: join_id,
				last_message_id: last_id,
				back: {
					$sender: this
				}
			},
			this.receiveCallback,
			true
		);
	}, 
	sendMessageCallback: function (result)
	{
	
	},
	clearChatInput: function ()
	{
	},
	sendMessage: function (params)
	{
		this.clearChatInput (); 
		
		if (!params.message)
		{
			return;
		}
		
		params.back = { $sender: this };
		
		Controller.call (
			'Chat_Message/send',
			params,
			(typeof params.dont_callback != 'undefined') 
				? function () { } 
				: this.sendMessageCallback,
			true
		);
	},
	receiveCallback: function (result)
	{

	}
});
