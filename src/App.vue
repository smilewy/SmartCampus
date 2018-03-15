<template>
  <div id="app">
    <div id="login" class="clear_fix" v-if="loginState">
      <div class="login_content">
        <div class="student_entrance">
          <i class="el-icon-d-arrow-right"> 新生专用入口</i>
        </div>
        <el-row class="login_text">
          <el-col :span="12" class="login_text_l">
            <img src="./assets/img/login/logo_n.png" alt="logo">
          </el-col>
          <el-col :span="12" class="login_text_r">
            <el-row class="login_entrance">
              <h3>登录</h3>
              <p>LOGIN</p>
              <el-row class="login_entrance_input">
                <div><input type="text" placeholder="请输入你的账号" v-model="username"></div>
                <div class="login_line"></div>
                <div class="login_pwd">
                  <div v-show="passwordState">
                    <input type="text" placeholder="请输入你的密码" v-model="password">
                    <img src="./assets/img/login/login_eye_normal.png" alt="显示密码" class="login_pwd_show"
                         @click="passwordState=!passwordState">
                  </div>
                  <div v-show="!passwordState">
                    <input type="password" placeholder="请输入你的密码" v-model="password">
                    <div class="login_pwd_hide" @click="passwordState=!passwordState">
                      <img src="./assets/img/login/login_eye_pre.png" alt="隐藏密码">
                      <p>隐藏密码</p>
                    </div>
                  </div>
                </div>
                <div class="mistakeMsg" v-show="mistake">{{mistakeMsg}}</div>
              </el-row>
              <el-row class="login_pwd_btn">
                <el-col :span="12" class="login_pwd_btnL">
                  <el-checkbox v-model="pwdChecked">记住密码</el-checkbox>
                </el-col>
                <el-col :span="12" class="login_pwd_btnR"><a href="javascript:void(0)" @click="showMistake(1)"
                                                             class="forgetPwd">忘记密码？</a></el-col>
              </el-row>
              <el-row class="login_btn" :class="{'login_btn_active':isLogin}">
                <div @click="login">
                  点击登录
                </div>
              </el-row>
              <el-row type="flex" justify="center" class="login_type">
                <el-col :span="5"><span class="login_type_line"></span></el-col>
                <el-col :span="10">
                  <img src="./assets/img/login/login_qq_n.png" alt="qq"><img class="weixin"
                                                                             src="./assets/img/login/login_wechat_n.png"
                                                                             alt="weixin">
                </el-col>
                <el-col :span="5"><span class="login_type_line"></span></el-col>
              </el-row>
            </el-row>
          </el-col>
        </el-row>
      </div>
    </div>
    <div class="common clear_fix" v-else>
      <div class="common_LContainer">
        <div class="logo_con">
          <img src="./assets/img/commonNav/home_logo.png"/>
        </div>
        <div class="Nav_container">
          <div class="Nav_container_main">
            <nav-Bar :data.sync="NavInfo" @setJumpPage="jumpPage"/>
          </div>
        </div>
      </div>
      <div class="common_RContainer">
        <div class="common_RContainer_main">
          <div class="common_RHeader" :style="isHome?homeHeaderCss:NothomeHeaderCss">
            <div class="weatherCon">
              <div class="g-weatherImg">
                <iframe allowtransparency="true" frameborder="0" width="180" height="36" scrolling="no"
                        src="//tianqi.2345.com/plugin/widget/index.htm?s=3&z=2&t=0&v=0&d=3&bd=0&k=&f=ffffff&ltf=ffffff&htf=ffffff&q=1&e=1&a=1&c=54511&w=180&h=36&align=center"></iframe>
              </div>
            </div>
            <div class="rightCon">
              <div class="userCon">
                <div class="g-userCon">
                  <img :src="userInfo.logo"/>
                </div>
                <span v-text="userInfo.name"></span>
              </div>
              <router-link to="/viewAlerts">
                <div class="emailCon" :class="NoticeData==true?'emailConBefore':'emailCon'">
                  <img src="./assets/img/commonNav/icon_xiaoxi.png"/>
                </div>
              </router-link>
              <div class="shareCon" @click="signOutClick">
                <img src="./assets/img/commonNav/icon_signout.png" title="退出登录"/>
              </div>
            </div>
          </div>
          <div class="common_RContent clear_fix" :style="isHome?homeContentCss:NothomeContentCss">
            <router-view @on-notice-change="noticeChange"></router-view>
          </div>
        </div>
      </div>
    </div>

    <textarea id="rsakey">-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQD0oHzqDR0ItXeaBoocKCmMx+uY
UBTCllaeWRiwtLezLEohIw0L2BFHaUlhzomD1N4Dzg9B23nAkpVRpdGydbQuiOY2
iNpwb93W43tXalR5GyeGYwduV9fr2XVcFOlB8+I1zlTfaY5CGan21XUcerTDTXMS
k/wBaqgifUF1YyTvxwIDAQAB
-----END PUBLIC KEY-----
</textarea>
  </div>
</template>
<script>
  import {mapState} from 'vuex'
  import '../static/jcookie/jquery.cookie'
  import req from '@/assets/js/common'
  import navBar from '@/components/navBar'
  import {
    homeSignOut,//退出登录
  } from '@/api/http'

  import CryptoJS from 'crypto-js'

    import JSEncrypt from 'jsencrypt'

  export default {
    name: 'app',
    data() {
      return {
        // 加密密钥 必须为8/16/32
        cryptoKey: 'ifberger',

        NoticeLength: 0,
        NoticeData: [],
        /*wy*/
        username: '',
        password: '',
        passwordState: true,
        pwdChecked: false,
        mistake: false,
        mistakeMsg: '',
        loginState: true,
        /*wqq*/
        isHome: true,//home主体内容header高度不一样
        homeHeaderCss: {height: '10.375rem', boxShadow: ''},
        NothomeHeaderCss: {height: '5.625rem', boxShadow: '0 0.3125rem 0.625rem rgba(0,0,0,0.2)'},
        homeContentCss: {bottom: '6.75rem', position: 'relative', zIndex: '10'},
        NothomeContentCss: {bottom: '', position: '', zIndex: ''},
        ops: {
          background: "#cecece",
          width: '5px',
          deltaY: '50',
          float: 'right'
        },
      }
    },
    components: {
      'nav-Bar': navBar
    },
    computed: {
      /*wy*/
      isLogin() {   //判断账号密码是否填写,登录按钮能否点击
        return (this.username && this.password);
      },
      userInfo() {   //获取登录后的用户信息
        return this.$store.state.userInfo;
      },
      /*wqq*/
      ...mapState(['NavInfo', 'userInfo'])
    },
    methods: {
        /** AES加密
         * @augments 待加密字符
         */
        cryptoNecrypt( encryptStr ){
            return CryptoJS.AES.encrypt( encryptStr , CryptoJS.enc.Utf8.parse( this.cryptoKey ), {
                mode: CryptoJS.mode.ECB,
                padding: CryptoJS.pad.Pkcs7
            });
        },

        /** AES解密
         * @augments 通过AES加密后的数据
         */
        cryptoDecrypt( decryptStr ){
            let decrypt = CryptoJS.AES.decrypt( decryptStr, CryptoJS.enc.Utf8.parse( this.cryptoKey ), {
                mode: CryptoJS.mode.ECB,
                padding: CryptoJS.pad.Pkcs7
            });
            return decrypt.toString( CryptoJS.enc.Utf8 );
        },

      jumpPage(val) {
        closeOther(this.NavInfo);
        if (!val.childs) {
          val.ifHave = 1;
        } else {
          val.ifHave = val.ifHave === 1 ? 0 : 1;
        }
        if (val.url) {
          this.$router.push('/' + val.url);
        }
        function closeOther(tree) {
          tree.forEach(subVal => {
            if (subVal.level >= val.level && subVal.modelId !== val.modelId) {
              subVal.ifHave = 0;
            }
            if (subVal.childs) {
              closeOther(subVal.childs);
            }
          })
        }
      },
      Notice() {
        if (sessionStorage.userInfo) {
          req.ajaxSendOther('school/Home/lists', 'post', {}, (res) => {
            if (res.data.length) {
              this.NoticeData = res.char
            }
          })
        }
      },
      noticeChange(len) {
        this.NoticeLength = len;
      },
      /*send ajax*/
      /*退出登录*/
      signOutClick() {
        homeSignOut().then(data => {
          if (data.statu) {
            this.vmMsgSuccess('退出成功！');
            this.loginState = true;
            sessionStorage.userInfo = '';
            if ($.cookie('_ac') && $.cookie('_gu')) {

              this.pwdChecked = true;
              this.username = $.cookie('_ac');
              this.password = this.cryptoDecrypt( $.cookie('_gu') );
            } else {
              this.pwdChecked = false;
            }
            this.$router.push('/Home');
          }
          else {
            this.loginState = false;
            this.vmMsgError('退出失败！');
          }
        });
      },
      /*wy*/
      showMistake(type, msg) {
        if (type == 1) {
          this.mistakeMsg = '忘记密码？请联系本班老师';
        } else {
          this.mistakeMsg = msg;
        }
        this.mistake = true;
      },
      login() {
        var self = this;
        if (!self.isLogin) {
          return false;
        }
        let loadingIns = this.vmLoadingFull( '登录中，请稍后...' );
        let RSAEncrypt = JSEncrypt.JSEncrypt;

        let encrypt = new RSAEncrypt();
        encrypt.setPublicKey(document.getElementById('rsakey').value);

        var param = {
          account: encrypt.encrypt(self.username),
          password: encrypt.encrypt(self.password)
        };
        req.ajaxSend('/school/user/doLogin', 'post', param, function (res) {
            loadingIns.close();
          if (res.statu == 1) {   //登录成功
            /*wqq*/
            self.NavTotalArr = res.model;
            /*wy*/
            self.loginState = false;
            self.$store.dispatch({type: 'initNavAction', NavInfo: res.model});
            self.$store.dispatch({type: 'CommitChangeUser', userInfo: res.user});
            sessionStorage.userInfo = JSON.stringify(res);
            if (self.pwdChecked) {
              $.cookie('_ac', self.username, {expires: 7, path: '/'});
              $.cookie('_gu', self.cryptoNecrypt( self.password ), {expires: 7, path: '/'});
            } else {
              $.cookie('_ac', null, {expires: -1, path: '/'});
              $.cookie('_gu', null, {expires: -1, path: '/'});
            }
            self.mistake = false;
          } else {
            self.showMistake(0, res.message);
          }
        }, false);
      },
    },
    created() {
      this.Notice();
      let userInfo, self = this;
     /*wy*/
      if (sessionStorage.userInfo) {  //先判断session是否存在，会话是否结束，避免在当前位置刷新跳出登录
        self.loginState = false;
        userInfo = JSON.parse(sessionStorage.userInfo);
        self.$store.dispatch({type: 'CommitChangeUser', userInfo: userInfo.user});
        self.$store.dispatch({type: 'initNavAction', NavInfo: userInfo.model});
      }
      else {   //判断是否记住密码
        self.loginState = true;
        if ($.cookie('_ac') && $.cookie('_gu')) {
          self.pwdChecked = true;
          self.username = $.cookie('_ac');
          self.password = self.cryptoDecrypt( $.cookie('_gu') );
        } else {
          self.pwdChecked = false;
        }
        self.$router.push('/Home');
      }
    }
  }
</script>
<style lang="less" scoped>
  @import 'style/App.less';

  #app {
    font-family: 'Avenir', Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    color: #2c3e50;

  .emailConBefore {
    position: relative;
  }

  .emailConBefore:before {
    position: absolute;
    content: '';
    background-color: red;
    width: .6rem;
    height: .6rem;
    border-radius: .6rem;
    display: block;
    left: 1.75rem;
    top: .2rem;
  }

  }

  #login {
    width: 100%;
    height: 100%;
    position: fixed;
    top: 0;
    left: 0;
    background-image: url(../static/img/bg.png);
    -webkit-background-size: cover;
    background-size: cover;
  }

  .login_content {
    width: 85.38rem;
    height: 48rem;
    position: absolute;
    top: 50%;
    left: 50%;
    -webkit-transform: translate(-50%, -50%);
    -moz-transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    -o-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
    background-color: #fff;
    border-radius: .8rem;
  }

  .student_entrance {
    position: absolute;
    right: 4rem;
    top: 2.12rem;
    width: 8.75rem;
    height: 2.5rem;
    line-height: 2.5rem;
    border: 1px solid #4ba8ff;
    text-align: center;
    font-size: 0.875rem;
    color: #4ba8ff;
    cursor: pointer;
  }

  .student_entrance:before {
    content: '';
    position: absolute;
    display: block;
    height: 1px;
    background-color: #4ba8ff;
    width: 4.1rem;
    top: 50%;
    right: -4.1rem;
  }

  .login_text {
    height: 33.5rem;
    margin: 7.25rem 0;
  }

  .login_text > div {
    height: 100%;
    text-align: center;
  }

  .login_text_l {
    border-right: 1px solid #a3a3a3;
  }

  .login_text_l img {
    width: 12.5rem;
    height: auto;
    margin: 12rem 0;
  }

  .login_pwd {
    position: relative;
  }

  .login_pwd img {
    width: 1.875rem;
  }

  .login_pwd_hide, .login_pwd_show {
    cursor: pointer;
    position: absolute;
    right: 0;
    top: 50%;
    -webkit-transform: translateY(-50%);
    -moz-transform: translateY(-50%);
    -ms-transform: translateY(-50%);
    -o-transform: translateY(-50%);
    transform: translateY(-50%);
  }

  .login_pwd_hide {
    font-size: .75rem;
    color: #999999;
  }

  .login_text_r {
    padding-top: 1rem;
  }

  .login_entrance {
    width: 25.625rem;
    margin: auto;
  }

  .login_entrance > h3 {
    font-size: 1.875rem;
    color: #212121;
  }

  .login_entrance > p {
    margin-top: .5rem;
    font-size: 1.125rem;
    color: #212121;
  }

  .login_entrance_input {
    position: relative;
    margin: 2.875rem auto 0;
    height: 10.625rem;
    padding: 0 1.5rem;
    border: 1px solid #a3a3a3;
  }

  .mistakeMsg {
    color: #fff;
    font-size: 0.875rem;
    background-color: #ff6772;
    text-align: center;
    padding: .2rem 0;
    width: 54%;
    position: absolute;
    top: -1px;
    left: 50%;
    -webkit-transform: translateX(-50%);
    -moz-transform: translateX(-50%);
    -ms-transform: translateX(-50%);
    -o-transform: translateX(-50%);
    transform: translateX(-50%);
  }

  .login_entrance_input > div:nth-child(3) {
    padding-right: 2rem;
  }

  .login_entrance_input input {
    width: 100%;
    height: 5rem;
    font-size: 1.125rem;
    border: none;
  }

  .login_entrance_input input:focus {
    outline: none;
  }

  .login_entrance_input :-moz-placeholder { /* Mozilla Firefox 4 to 18 */
    color: #878787;
  }

  .login_entrance_input ::-moz-placeholder { /* Mozilla Firefox 19+ */
    color: #878787;
  }

  .login_entrance_input input:-ms-input-placeholder {
    color: #878787;
  }

  .login_entrance_input input::-webkit-input-placeholder {
    color: #878787;
  }

  .login_pwd_btn {
    margin-top: 1.25rem;
  }

  .login_pwd_btn .el-checkbox__label {
    font-size: 1rem;
  }

  .login_pwd_btnL {
    text-align: left;
  }

  .login_pwd_btnR {
    text-align: right;
    font-size: 1rem;
  }

  .login_btn {
    margin-top: 3.125rem;
    height: 2.875rem;
    line-height: 2.875rem;
    font-size: 1.125rem;
    color: #fff;
    background-color: #a5d3ff;
  }

  .login_btn_active {
    background-color: #4ba8ff;
    cursor: pointer;
  }

  .login_type {
    margin-top: 2.25rem;
  }

  .login_type img {
    width: 2.5rem;
    height: auto;
    cursor: pointer;
  }

  .login_type img.weixin {
    margin-left: 1.75rem;
  }

  .login_type_line, .login_line {
    border-bottom: 1px solid #a3a3a3;
  }

  .login_type_line {
    display: block;
    height: 1.25rem;
  }
</style>




