$(document).ready(function(){"use strict";var e=window.console||{log:function(){}},t=$("body");!function(){var o=$(".img-container > img"),a=$("#download"),r=$("#dataX"),p=$("#dataY"),n=$("#dataHeight"),c=$("#dataWidth"),i=$("#dataRotate"),d=$("#dataScaleX"),l=$("#dataScaleY"),s={aspectRatio:16/9,preview:".img-preview",crop:function(e){r.val(Math.round(e.x)),p.val(Math.round(e.y)),n.val(Math.round(e.height)),c.val(Math.round(e.width)),i.val(e.rotate),d.val(e.scaleX),l.val(e.scaleY)}};o.on({"build.cropper":function(t){e.log(t.type)},"built.cropper":function(t){e.log(t.type)},"cropstart.cropper":function(t){e.log(t.type,t.action)},"cropmove.cropper":function(t){e.log(t.type,t.action)},"cropend.cropper":function(t){e.log(t.type,t.action)},"crop.cropper":function(t){e.log(t.type,t.x,t.y,t.width,t.height,t.rotate,t.scaleX,t.scaleY)},"zoom.cropper":function(t){e.log(t.type,t.ratio)}}).cropper(s),$.isFunction(document.createElement("canvas").getContext)||$("button[data-method='getCroppedCanvas']").prop("disabled",!0),"undefined"==typeof document.createElement("cropper").style.transition&&($("button[data-method='rotate']").prop("disabled",!0),$("button[data-method='scale']").prop("disabled",!0)),"undefined"==typeof a[0].download&&a.addClass("disabled"),t.on("click","[data-method]",function(){var t,r,p=$(this),n=p.data();if(!p.prop("disabled")&&!p.hasClass("disabled")&&o.data("cropper")&&n.method){if(n=$.extend({},n),"undefined"!=typeof n.target&&(t=$(n.target),"undefined"==typeof n.option))try{n.option=JSON.parse(t.val())}catch(c){e.log(c.message)}if(r=o.cropper(n.method,n.option,n.secondOption),"horizontal"===n.flip&&$(this).data("option",-n.option),"vertical"===n.flip&&$(this).data("secondOption",-n.secondOption),"getCroppedCanvas"===n.method&&r&&($("#getCroppedCanvasModal").modal().find(".modal-body").html(r),a.hasClass("disabled")||a.attr("href",r.toDataURL())),$.isPlainObject(r)&&t)try{t.val(JSON.stringify(r))}catch(c){e.log(c.message)}}}).on("keydown",function(e){if(o.data("cropper"))switch(e.which){case 37:e.preventDefault(),o.cropper("move",-1,0);break;case 38:e.preventDefault(),o.cropper("move",0,-1);break;case 39:e.preventDefault(),o.cropper("move",1,0);break;case 40:e.preventDefault(),o.cropper("move",0,1)}});var u,f=$("#inputImage"),h=window.URL||window.webkitURL;h?f.change(function(){var e,a=this.files;o.data("cropper")&&a&&a.length&&(e=a[0],/^image\/\w+$/.test(e.type)?(u=h.createObjectURL(e),o.one("built.cropper",function(){h.revokeObjectURL(u)}).cropper("reset").cropper("replace",u),f.val("")):t.tooltip("Please choose an image file.","warning"))}):f.prop("disabled",!0).parent().addClass("disabled"),$(".docs-options :checkbox").on("change",function(){var e,t,a=$(this);o.data("cropper")&&(s[a.val()]=a.prop("checked"),e=o.cropper("getCropBoxData"),t=o.cropper("getCanvasData"),s.built=function(){o.cropper("setCropBoxData",e),o.cropper("setCanvasData",t)},o.cropper("destroy").cropper(s))})}()});