<template>
  <div class="newDcoument">
    <h3>新建公文</h3>
    <el-row class="newDoc-top" type="flex" align="middle">
      <el-col :span="24">
        <el-col :span="6">
          <el-col :span="4" class="newDoc-top-title">标题：</el-col>
          <el-col :span="20">
            <el-input v-model="param.title"></el-input>
          </el-col>
        </el-col>
        <el-col :span="6" :offset="2">
          <el-col :span="4" class="newDoc-top-title">类型：</el-col>
          <el-col :span="20">
            <el-select style="width: 100%" v-model="param.name" placeholder="请选择" @change="changeName()">
              <el-option label="通用公文流转" value="0"></el-option>
              <el-option
                v-for="item in options"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </el-col>
        </el-col>
        <el-col :span="8" :offset="2" v-if="param.name==='0'">
          <el-col :span="8" class="newDoc-top-title">通用公文流转对象：</el-col>
          <el-col :span="16">
            <el-input v-model="param.target"></el-input>
          </el-col>
        </el-col>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="24" class="newDoc-text-title">编辑内容：</el-col>
    </el-row>
    <el-col :span="24" style="margin-top: -.8rem;">
      <el-col :span="17" class="Appset-left">
        <el-row class="noticeUeditor">
          <quill-editor style="height:92%" v-model="param.content"></quill-editor>
        </el-row>
      </el-col>
      <el-col :span="6" :offset="1" class="Appset-right">
        <div class="Appset-right-title">待选流转对象
          <span v-if="param.name==='0'">（单选）</span>
          <span v-if="param.name!=='0'">（不可选）</span>
        </div>
        <el-col :span="22" :offset="1" class="Appset-right-input1">
          <el-input
            placeholder="请输入查询关键字"
            v-model="filterText"
            @change="handleIconClick">
          </el-input>
        </el-col>
        <div class="Appset-right-border"></div>
        <el-col :span="24">
          <el-tree
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            class="filter-tree"
            :data="approveData"
            :props="defaultProps"
            default-expand-all
            @node-click="handleNodeClick"
            :filter-node-method="filterNode"
            ref="tree2">
          </el-tree>
        </el-col>
      </el-col>
    </el-col>
    <el-col :span="24">
      <el-row class="newDoc_footer">
        <el-col :span="22">
          <el-row>
            <el-col :span="5">
              <el-col :span="7" style="margin-top: .3rem">附件：</el-col>
              <el-col :span="16" style="border-right: 1px solid #d2d2d2">
                <div class="uploadFile">
                  <el-button>选择附件</el-button>
                  <input type="file" accept=".xlsx,.xlsm,.xls.doc,.docx,.ppt" class="file_input" @change="sendFile">
                </div>
              </el-col>
            </el-col>
            <el-col :span="19" class="fileLists">
              <div v-for="(file,index) in fileList">
                <img v-if="file.fileType==1"
                     src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_ppt.png"
                     alt="">
                <img v-if="file.fileType==2"
                     src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_word.png"
                     alt="">
                <img v-if="file.fileType==3"
                     src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_excel.png"
                     alt="">
                <span :title="file.name">{{file.name}}</span>
                <i class="el-icon-close" @click="deleteFile(index)"></i>
              </div>
            </el-col>
          </el-row>
        </el-col>
        <el-col :span="2">
          <el-button type="primary" :loading="save===true" @click="PublishnewDoc()">发布</el-button>
        </el-col>
        <!--<el-col :span="2" :offset="1">-->
        <!--<el-button @click="SaveDraft()">保存草稿</el-button>-->
        <!--</el-col>-->
      </el-row>
    </el-col>
  </div>
</template>
<script>
  import Vue from 'vue'
  import req from '../../../../../assets/js/common'
  import ElCol from "element-ui/packages/col/src/col";
  export default{
    data(){
      return {
        isLoading:false,
        save:false,
        options: [{
          value: '',
        }],
        fileList: [],
        param: {
          title: '',
          name:'',
          target:'',
          isCommon:'',
//          approver:{},
          aId:'',
          aName:'',
          type: 'create',
          content: '',
        },
        filterText: '',
        approveData:[],
        defaultProps: {
          children: 'children',
          label: 'label'
        },
      }
    },
    created(){
      let param={
        kind:4
      };
      req.ajaxSend('/school/WorkDemand/getName','post',param,(res)=>{
        this.options = res.map(val=>({value:val.name}))
      });
    },
    methods: {
      sendFile() {   //上传文件
        var fileType, suffix, file = $('.file_input').prop('files')[0];
        if (!file) {
          return false;
        }
        $('.file_input').val('');
        if (this.fileList.length >= 3) {
          this.vmMsgWarning('最多只能上传三个附件！');
          return false;
        }
        var sAry = file.name.split('.'), sL = sAry.length - 1;
        suffix = sAry[sL];
        switch (suffix) {
          case 'ppt':
            fileType = 1;
            break;
          case 'docx':
          case 'doc':
            fileType = 2;
            break;
          case 'xlsx':
          case 'xlsm':
          case 'xls':
            fileType = 3;
            break;
          default:
            this.vmMsgWarning('只能上传word、xlsx、xlsm、xls、ppt格式文件！');
            return false;
        }
        for (let obj of this.fileList) {
          if (file.name == obj.name) {
            this.vmMsgWarning('你已添加过该文件！');
            return false;
          }
        }
        this.fileList.push({'fileType': fileType, 'name': file.name, 'file': file});
      },
      deleteFile(idx){   //删除文件
        this.fileList.splice(idx,1);
      },
      PublishnewDoc(){
        if(this.param.name==='0'){
          var sendFormData=new FormData();
          for (let i of this.fileList){
            sendFormData.append('accessory[]', i.file);
          }
          sendFormData.append('content', this.param.content);
          sendFormData.append('name','通用公文流转');
          sendFormData.append('title',this.param.title);
          sendFormData.append('type','create');
          sendFormData.append('isCommon','Y');
          sendFormData.append('aId',this.param.aId);
          sendFormData.append('aName',this.param.aName);
        }else{
          var sendFormData=new FormData();
          for (let i of this.fileList){
            sendFormData.append('accessory[]', i.file);
          }
          sendFormData.append('content', this.param.content);
          sendFormData.append('name',this.param.name);
          sendFormData.append('title',this.param.title);
          sendFormData.append('type',this.param.type);
          sendFormData.append('isCommon','N');
        }
        if(!this.param.title){
          this.vmMsgWarning('请输入公文标题');
          return;
        }
        if(!this.param.name){
          this.vmMsgWarning('请选择发布类型');
          return;
        }
        if(!this.param.target&&this.param.name==='0'){
          this.vmMsgWarning('请输入通用公文流转对象');
          return;
        }
        if(!this.param.content){
          this.vmMsgWarning('请完善公文内容');
          return;
        }
        this.$confirm('是否确定新建该公文流转?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.save=true;
          req.ajaxFile('/school/WorkDemand/createDoc','post',sendFormData,(res)=>{
            this.save=false;
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
            }else {
              this.vmMsgError(res.msg);
            }
          });
          this.param={
            title: '',
            name:'',
            target:'',
            isCommon:'',
            aId:'',
            aName:'',
            type: 'create',
            content: '',
          };
        }).catch(() => {

        });
      },
      SaveDraft(){
        if(this.param.name==='0'){
          var sendFormData=new FormData();
          for (let i of this.fileList){
            sendFormData.append('accessory[]', i.file);
          }
          sendFormData.append('content', contethis.param.contentnttext);
          sendFormData.append('name',this.param.name);
          sendFormData.append('title',this.param.title);
          sendFormData.append('isCommon','Y');
          sendFormData.append('aId',this.param.aId);
          sendFormData.append('aName',this.param.aName);
        }else{
          var sendFormData=new FormData();
          for (let i of this.fileList){
            sendFormData.append('accessory[]', i.file);
          }
          sendFormData.append('content', this.param.content);
          sendFormData.append('name',this.param.name);
          sendFormData.append('title',this.param.title);
          sendFormData.append('isCommon','N');
        }
        if(!this.param.title){
          this.vmMsgWarning('请输入公文标题');
          return;
        }
        if(!this.param.name){
          this.vmMsgWarning('请选择发布类型');
          return;
        }
        if(!this.param.target&&this.param.name==='0'){
          this.vmMsgWarning('请输入通用公文流转对象');
          return;
        }
        if(!this.param.content){
          this.vmMsgWarning('请完善公文内容');
          return;
        }
        this.$confirm('是否确定将该公文保存为草稿?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxFile('/school/WorkDemand/createDoc','post',sendFormData,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
            }else {
              this.vmMsgError(res.msg);
            }
          });
        }).catch(() => {

        });
      },
      changeName(){
        this.getApproveList();
      },
      getApproveList(){
        this.isLoading=true;
        function suit2tree(data){
          var tree = [],
            final = [];
          if(isArray(data)){
            tree = data;
          }else{
            tree = obj2arr(data);
          }
          tree.forEach(function(val){
            var object = {};
            val.label = val.name || val.custom_id;
            if(val.id || val.custom_id)
              object.id = val.id || val.custom_id;
            if(val.custom_id)
              delete val.custom_id;
            if(val.name)
              delete val.name;
            val.children = deepCopy(val);
            if(val.label&&val.id){
              delete val.children;
            }else{
              val.children = suit2tree(val.children);
              object.children = val.children;
            }
            object.label = val.label;
            if(object.label&&object.label!=='children')
              final.push(object);
          });
          return final;
        }
        function obj2arr(data,key_name){
          if(!isArray(data)){
            var arr = [];
            for(var key in data){
              var object = data[key];
              if(typeof object === 'object'){
                object[key_name||'custom_id'] = key;
                arr.push(object);
              }
            }
            return arr;
          }else{
            return data;
          }
        }
        function isArray(data){
          return data&&!!data.join;
        }
        function deepCopy(data){
          return JSON.parse(JSON.stringify(data));
        }
        if(this.param.name==='0'){
          var param={
            isCommon:'Y'
          };
        }else{
          var param={
            isCommon:'N',
            name:this.param.name
          };
        }
        req.ajaxSend('/school/WorkDemand/userLists','post',param,(res)=>{
          this.approveData=suit2tree(res.data);
          this.isLoading=false;
        });
      },
      handleIconClick() {},
      handleNodeClick(data){
        if(data.children)return;
        if(this.param.name!=='0')return;
        this.param.target=data.label;
        this.param.aId=data.id;
        this.param.aName=data.label;
      },
      filterNode(value, data) {
        if (!value) return true;
        return data.label.indexOf(value) !== -1;
      },
    },
    watch: {
      filterText(val) {
        this.$refs.tree2.filter(val);
      }
    },
  }
</script>
<style lang="less" scoped>
  .newDcoument{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    .Appset-left,.Appset-right{
      height:33.5rem;
      margin-top: 2rem;
      border-radius: .4rem;
      overflow-y: auto;
      margin-bottom: 1rem;
      box-shadow: 0 0.1rem 0.1rem 0.12rem rgba(0, 0, 0, 0.09) inset;
    }
    .noticeUeditor {
      height: 33.5rem;
    }
    .Appset-right{
      border: 1px solid #d2d2d2;
    }
    .Appset-right-title{
      padding: .8rem 0 .8rem .8rem;
      font-weight: bold;
      font-size: 0.95rem;
    }
    .Appset-right-border{
      border: 1px solid #d2d2d2;
      margin-top:2.9rem;
    }
    .el-tree{
      border: none;
      margin-bottom:3rem;
    }
    .Appset-right-input .el-input__inner{
      height:36/16rem;
      border-radius:1.1rem ;
      border-color: #4da1ff;
    }
  }
  .newDoc-top{
    margin: 2rem 0;
  }
  .newDoc-top-title{
    margin-top: 0.5rem;
  }
  .newDoc-text-title{
    font-weight:bolder;
  }
  .newDoc-vueditor{
    height:33.5rem;
    border: 1px solid #c0c0c0;
    border-radius: 6px;
  }
  .newDoc_footer{
    margin-top: 24/16rem;
    padding: 1rem 2rem;
    .el-button {
      width: 110/16rem;
      border-radius: 18/16rem;
      padding: 10px 0;
    }
  }
  .fileLists > div {
    float: left;
    position: relative;
    font-size: 14/16rem;
    margin-left: 2rem;
    span {
      display: inline-block;
      max-width: 10rem;
      text-overflow: ellipsis;
      overflow: hidden;
      white-space: nowrap;
    }
    img {
      width: 22/16rem;
      margin-right: 14/16rem;
    }
    i {
      position: absolute;
      right: -10px;
      top: -5px;
      cursor: pointer;
      font-size: 12px;
    }
  }
  .uploadFile {
    display: inline-block;
    position: relative;
    .file_input {
      width: 100%;
      height: 36px;
      border-radius: 18/16rem;
      position: absolute;
      right: 0;
      top: 0;
      z-index: 1;
      -moz-opacity: 0;
      -ms-opacity: 0;
      -webkit-opacity: 0;
      opacity: 0; /*css属性——opcity不透明度，取值0-1*/
      filter: alpha(opacity=0); /*兼容IE8及以下--filter属性是IE特有的，它还有很多其它滤镜效果，而filter: alpha(opacity=0); 兼容IE8及以下的IE浏览器(如果你的电脑IE是8以下的版本，使用某些效果是可能会有一个允许ActiveX的提示,注意点一下就ok啦)*/
      cursor: pointer;
    }
  }
</style>
<style lang="less">
  .Appset-right-input1 .el-input__inner{
    height:28/16rem;
    border-radius:.8rem ;
  }
</style>
