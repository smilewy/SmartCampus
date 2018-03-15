// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
//@代表src文件夹，在base.config.js中的alias配置
import Vue from 'vue'
import App from './App.vue'
import ElementUI from 'element-ui'
import VueQuillEditor from 'vue-quill-editor'
import 'element-ui/lib/theme-chalk/index.css'
import 'quill/dist/quill.core.css'
import 'quill/dist/quill.snow.css'
import 'quill/dist/quill.bubble.css'
import '@/style/css/common.css'
import store from '@/vuex/store'
import router from '@/config/router'
import moment from 'moment'
import CommonApi from '@/api/common-api'

import '../static/UEditor/ueditor.config'
import '../static/UEditor/ueditor.all.min'/*../static/UEditor/ueditor.all*/
import '../static/UEditor/lang/zh-cn/zh-cn'
import '../static/UEditor/ueditor.parse.min'
import '../static/mCustomScrollbar/jquery.mCustomScrollbar.css'
import '../static/mCustomScrollbar/jquery.mousewheel.min'
import '../static/mCustomScrollbar/jquery.mCustomScrollbar.min.js'

var toolbarOptions = [
  ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
  ['blockquote', 'code-block'],

  [{ 'header': 1 }, { 'header': 2 }],               // custom button values
  [{ 'list': 'ordered'}, { 'list': 'bullet' }],
  [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
  [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
  [{ 'direction': 'rtl' }],                         // text direction

  [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
  [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

  [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
  [{ 'font': [] }],
  [{ 'align': [] }],
  [{ 'link':  function(value) {
    if (value) {
      var href = prompt('Enter the URL');
      this.quill.format('link', href);
    } else {
      this.quill.format('link', false);
    }
  } }],
  ['clean']                                         // remove formatting button
];
Vue.use(CommonApi);
Vue.use(ElementUI);
Vue.use(VueQuillEditor, {
  modules: {
    toolbar: toolbarOptions
  },
  placeholder: '请输入内容......'
});

Vue.filter('formatDate', function (data,formatString) {   //时间过滤器
  data=parseInt(data)*1000;
  formatString=formatString||'YYYY-MM-DD HH:mm:ss';
  return moment(data).format(formatString);
});

new Vue({
  router,
  store,
  render:h=>h(App),
  el: '#app',
  template: '<App/>',
  components: { App }
}).$mount('#app');
