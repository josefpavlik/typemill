const navcomponent = Vue.component('navigation', {
	template: '#navigation-template',
	props: ['name', 'parent', 'active', 'filetype', 'element', 'folder', 'level', 'url', 'root', 'freeze'],
	methods: {
		checkMove : function(evt)
		{			
			if(evt.dragged.classList.contains('folder') && evt.from.parentNode.id != evt.to.parentNode.id)
			{
				return false;				
			}
			if(evt.dragged.firstChild.className == 'active' && !editor.draftDisabled)
			{
				editor.errors.message = "Please save your changes before you move the file";
				return false;
			}
			return true;
		},
		onStart : function(evt)
		{
			/* delete error messages if exist */
			editor.errors.message = false;
		},
		onEnd : function(evt)
		{
			var locator = {
				'item_id': 			evt.item.id,
				'parent_id_from': 	evt.from.parentNode.id, 
				'parent_id_to': 	evt.to.parentNode.id, 
				'index_old': 		evt.oldIndex, 
				'index_new': 		evt.newIndex,
				'active':			evt.item.firstChild.className,
				'url':				document.getElementById("path").value,
				'csrf_name': 		document.getElementById("csrf_name").value,
				'csrf_value':		document.getElementById("csrf_value").value,				
			};
			
			if(locator.parent_id_from == locator.parent_id_to && locator.index_old == locator.index_new)
			{
				return
			}
			
			evt.item.classList.add("load");
			
			var self = this;
			
			self.$root.$data.freeze = true;
			self.errors = {title: false, content: false, message: false};
			
			var url = this.root + '/api/v1/article/sort';
			var method 	= 'POST';

			sendJson(function(response, httpStatus)
			{
				if(response)
				{
					self.$root.$data.freeze = false;
					var result = JSON.parse(response);
					
					if(result.errors)
					{
						editor.errors.message = result.errors;
					}
					if(result.url)
					{
						window.location.replace(result.url);
					}
					if(result.data)
					{
						evt.item.classList.remove("load");
						self.$root.$data.items = result.data;						
					}
				}
			}, method, url, locator );
		},
		getUrl : function(root, url)
		{
			return root + '/tm/content' + url
		},
		getLevel : function(level)
		{
			level = level.toString();
			level = level.split('.').length;
			return 'level-' + level;
		},
		getIcon : function(filetype)
		{
			if(filetype == 'file')
			{
				return 'icon-doc-text'
			}
			if(filetype == 'folder')
			{
				return 'icon-folder-empty'
			}
		},
		checkActive : function(active,parent)
		{
			if(active && !parent)
			{
				return 'active';
			}
			return 'inactive';
		}
	}
})

let navi = new Vue({
	el: "#navi",
	components: {
		'navcomponent': navcomponent,
	},
	data: {
		title: "Navigation",
		items: JSON.parse(document.getElementById("data-navi").dataset.navi),
		root: document.getElementById("main").dataset.url,
		freeze: false,
		modalWindow: "modal hide",		
	},
	methods:{
		onStart: function(evt){
			this.$refs.draggit[0].onStart(evt);			
		},
		onEnd: function(evt){
			this.$refs.draggit[0].onEnd(evt);
		},
		showModal: function(e){
			this.modalWindow = "modal show";
		},
		hideModal: function(e){
			this.modalWindow = "modal";
		},
	}
})