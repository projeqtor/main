//>>built
require({cache:{"url:dojox/form/resources/FileInputAuto.html":"<div class=\"dijitFileInput\">\r\n\t<input id=\"${id}\" name=\"${name}\" class=\"dijitFileInputReal\" type=\"file\" dojoAttachPoint=\"fileInput\" />\r\n\t<div class=\"dijitFakeInput\" dojoAttachPoint=\"fakeNodeHolder\">\r\n\t\t<input class=\"dijitFileInputVisible\" type=\"text\" dojoAttachPoint=\"focusNode, inputNode\" />\r\n\t\t<div class=\"dijitInline dijitFileInputText\" dojoAttachPoint=\"titleNode\">${label}</div>\r\n\t\t<div class=\"dijitInline dijitFileInputButton\" dojoAttachPoint=\"cancelNode\" dojoAttachEvent=\"onclick:reset\">${cancelText}</div>\r\n\t</div>\r\n\t<div class=\"dijitProgressOverlay\" dojoAttachPoint=\"overlay\">&nbsp;</div>\r\n</div>\r\n"}});define("dojox/form/FileInputAuto",["dojo/_base/declare","dojo/_base/lang","dojo/_base/fx","dojo/_base/window","dojo/dom-style","dojo/_base/sniff","dojo/text!./resources/FileInputAuto.html","dojox/form/FileInput","dojo/io/iframe"],function(_1,_2,fx,_3,_4,_5,_6,_7,_8){var _9=_1("dojox.form.FileInputAuto",_7,{url:"",blurDelay:2000,duration:500,uploadMessage:"Uploading ...",triggerEvent:"onblur",_sent:false,templateString:_6,onBeforeSend:function(){return {};},startup:function(){this._blurListener=this.connect(this.fileInput,this.triggerEvent,"_onBlur");this._focusListener=this.connect(this.fileInput,"onfocus","_onFocus");this.inherited(arguments);},_onFocus:function(){if(this._blurTimer){clearTimeout(this._blurTimer);}},_onBlur:function(){if(this._blurTimer){clearTimeout(this._blurTimer);}if(!this._sent){this._blurTimer=setTimeout(_2.hitch(this,"_sendFile"),this.blurDelay);}},setMessage:function(_a){this.overlay.removeChild(this.overlay.firstChild);this.overlay.appendChild(document.createTextNode(_a));},_sendFile:function(e){if(this._sent||this._sending||!this.fileInput.value){return;}this._sending=true;_4.set(this.fakeNodeHolder,"display","none");_4.set(this.overlay,{opacity:0,display:"block"});this.setMessage(this.uploadMessage);fx.fadeIn({node:this.overlay,duration:this.duration}).play();var _b;if(_5("ie")<9||(_5("ie")&&_5("quirks"))){_b=document.createElement("<form enctype=\"multipart/form-data\" method=\"post\">");_b.encoding="multipart/form-data";}else{_b=document.createElement("form");_b.setAttribute("enctype","multipart/form-data");}_b.appendChild(this.fileInput);_3.body().appendChild(_b);_8.send({url:this.url,form:_b,handleAs:"json",handle:_2.hitch(this,"_handleSend"),content:this.onBeforeSend()});},_handleSend:function(_c,_d){this.overlay.removeChild(this.overlay.firstChild);this._sent=true;this._sending=false;_4.set(this.overlay,{opacity:0,border:"none",background:"none"});this.overlay.style.backgroundImage="none";this.fileInput.style.display="none";this.fakeNodeHolder.style.display="none";fx.fadeIn({node:this.overlay,duration:this.duration}).play(250);this.disconnect(this._blurListener);this.disconnect(this._focusListener);_3.body().removeChild(_d.args.form);this.fileInput=null;this.onComplete(_c,_d,this);},reset:function(e){if(this._blurTimer){clearTimeout(this._blurTimer);}this.disconnect(this._blurListener);this.disconnect(this._focusListener);this.overlay.style.display="none";this.fakeNodeHolder.style.display="";this.inherited(arguments);this._sent=false;this._sending=false;this._blurListener=this.connect(this.fileInput,this.triggerEvent,"_onBlur");this._focusListener=this.connect(this.fileInput,"onfocus","_onFocus");},onComplete:function(_e,_f,_10){}});_1("dojox.form.FileInputBlind",_9,{startup:function(){this.inherited(arguments);this._off=_4.get(this.inputNode,"width");this.inputNode.style.display="none";this._fixPosition();},_fixPosition:function(){if(_5("ie")){_4.set(this.fileInput,"width","1px");}else{_4.set(this.fileInput,"left","-"+(this._off)+"px");}},reset:function(e){this.inherited(arguments);this._fixPosition();}});return _9;});