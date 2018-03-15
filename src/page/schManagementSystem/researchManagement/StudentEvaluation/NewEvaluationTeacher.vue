<template>
  <div class="NewEvaluationTeacher">
    <el-col :span="24">
      <h3>创建教学评价</h3>
    </el-col>
    <el-col :span="24">
      <el-col :span="6" class="Appset-right">
        <div class="Appset-right-title">学生评教范围</div>
        <div class="Appset-border"></div>
        <el-col :span="24">
          <el-tree
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            :data="scopeData"
            :props="defaultProps"
            accordion
            @node-click="handleNodeClick">
          </el-tree>
        </el-col>
      </el-col>
      <el-col :span="17" :offset="1" class="NewEvaluationTeacher-right">
        <el-col :span="18" :offset="3">
          <el-col :span="24">
            <el-col :span="4"><span style="color:#ffffff;font-size: 1.1rem">*</span> 学年学期：</el-col>
            <el-col :span="20">
              <el-input v-model="paramData.semester" :disabled="true"></el-input>
              <!--<el-select v-model="paramData.semester" placeholder="请选择" style="width: 100%;">-->
              <!--<el-option-->
              <!--v-for="item in options"-->
              <!--:key="item.term"-->
              <!--:label="item.yearname"-->
              <!--:value="item.yearname">-->
              <!--</el-option>-->
              <!--</el-select>-->
            </el-col>
          </el-col>
          <el-col :span="24" class="NewEvaluationTeacher-margin-top">
            <el-col :span="4"><span style="color: red;font-size: 1.1rem">*</span> 评教名称：</el-col>
            <el-col :span="20">
              <el-input placeholder="请输入评教名称" v-model="paramData.name"></el-input>
            </el-col>
          </el-col>
          <el-col :span="24" class="NewEvaluationTeacher-margin-top">
            <el-col :span="4"><span style="color: red;font-size: 1.1rem">*</span> 学生范围：</el-col>
            <el-col :span="20">
              <div class="Tagsdiv" @click="clickTags">
                <el-tag
                  :key="tag.id"
                  v-for="tag in scopename"
                  :closable="true"
                  :close-transition="false"
                  @close.stop="handleClose(tag)">
                  {{tag.label}}
                </el-tag>
              </div>
            </el-col>
          </el-col>
          <el-col :span="24" class="NewEvaluationTeacher-margin-top">
            <el-col :span="4"><span style="color: red;font-size: 1.1rem">*</span> 考评时间：</el-col>
            <el-col :span="20">
              <el-col :span="10">
                <el-date-picker
                  style="width: 100%"
                  v-model="paramData.startTime"
                  type="datetime"
                  :editable="false"
                  format="yyyy-MM-dd HH:mm"
                  placeholder="选择日期"
                  :picker-options="pickerOptions0">
                </el-date-picker>
              </el-col>
              <el-col :span="2" :offset="2">__</el-col>
              <el-col :span="10">
                <el-date-picker
                  style="width: 100%"
                  v-model="paramData.endTime"
                  type="datetime"
                  :editable="false"
                  placeholder="选择日期"
                  format="yyyy-MM-dd HH:mm"
                  :picker-options="pickerOptions1">
                </el-date-picker>
              </el-col>
            </el-col>
          </el-col>
          <el-col :span="24" class="NewEvaluationTeacher-margin-top">
            <el-col :span="4"><span style="color: red;font-size: 1.1rem">*</span> 评教方式：</el-col>
            <el-col :span="16">
              <!--<el-checkbox-group v-model="paramData.mode">-->
              <el-radio class="radio" v-model="paramData.mode" :label="1">分数</el-radio>
              <el-radio class="radio" v-model="paramData.mode" :label="2">满意度</el-radio>
              <el-radio class="radio" v-model="paramData.mode" :label="3">星级</el-radio>
              <!--<el-checkbox :label="1">分数</el-checkbox>-->
              <!--<el-checkbox :label="2" style="margin-left:3rem;">满意度</el-checkbox>-->
              <!--<el-checkbox :label="3" style="margin-left:3rem">星级</el-checkbox>-->
              <!--</el-checkbox-group>-->
            </el-col>
          </el-col>
          <el-col :span="24" class="NewEvaluationTeacher-margin-top">
            <el-col :span="4" style="margin-top: .48rem;"><span style="color:#ffffff;font-size: 1.1rem">*</span> 分数采样：</el-col>
            <el-col :span="20">
              每组最高分去除 <el-input type="number" min="0" v-model="paramData.max" style="width:12%"></el-input> 人， 每组最低分去除 <el-input v-model="paramData.min" min="0" type="number"  style="width:12%"></el-input> 人。
            </el-col>
          </el-col>
          <el-col :span="24" class="NewEvaluationTeacher-margin-top">
            <el-col :span="4" style="margin-top: .48rem;"><span style="color:#ffffff;font-size: 1.1rem">*</span> 评语字数：</el-col>
            <el-col :span="20">
              评语字数最少输入 <el-input type="number" min="0" v-model="paramData.comment" style="width:12%"></el-input> 字。
            </el-col>
          </el-col>
          <el-col :span="24">
            <el-button type="primary" class="NewEvaluationTeacher-right-btn" :loading="save===true" @click="createEvaluate()">创建</el-button>
          </el-col>
        </el-col>
      </el-col>
    </el-col>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import formatdata from '@/assets/js/date'
  export default{
    data(){
      return{
        isLoading:false,
        save:false,
        scopeData:[],
        defaultProps: {
          children: 'children',
          label: 'label'
        },
        options: [],
        pickerOptions0: {
          disabledDate:(time)=> {
            if(this.paramData.endTime){
              return time.getTime() > this.paramData.endTime;
            }
            return time.getTime() < Date.now()-8.64e7;
          }
        },
        pickerOptions1: {
          disabledDate:(time)=> {
            if(this.paramData.startTime){
              return time.getTime() < this.paramData.startTime;
            }
            return time.getTime() < Date.now()-8.64e7;
          }
        },
        scopename:[],
        paramData:{
          type:'create',
          semester:'',
          name:'',
          scope:[],
          startTime:'',
          endTime:'',
          mode:null,
          min:'',
          max:'',
          comment:''
        }
      }
    },
    created(){
      this.getTerms();
      this.getScopes();
    },
    methods:{
      clickTags(){
        this.vmMsgWarning( '请在左侧选择学生评教范围' ); 
      },
      getTerms(){
        req.ajaxSend('/school/StudentEvaluate/common','post',{func:'getSemester'},(res)=>{
          this.paramData.semester=res.yearname+' '+ res.term
        });
      },
      getScopes(){
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
            val.label = val.className || val.custom_id || val.root_name;
            if(val.custom_id)
              object.id = val.custom_id;
            if(val.classId)
              object.classId = val.classId;
            if(val.gradeId)
              object.gradeId = val.gradeId;
            if(val.custom_id)
              delete val.custom_id;
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
              if(object&&typeof object === 'object'){
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
        req.ajaxSend('/school/StudentEvaluate/common','post',{func:'getScope'},(res)=>{
          this.scopeData=suit2tree(obj2arr(res,'root_name'));
          this.isLoading=false;
        });
      },
      createEvaluate(){
        this.paramData.scope = this.scopename.map(val=>val.classId);
        if(!this.paramData.name){
          this.vmMsgWarning( '请输入评教名称！' ); return;
        }
        if(!this.paramData.scope.length){
          this.vmMsgWarning( '请在左侧学生评教范围中选择学生范围！' ); return;
        }
        if(!this.paramData.startTime){
          this.vmMsgWarning( '请选择考评开始时间' ); return;
        }
        if(!this.paramData.endTime){
          this.vmMsgWarning( '请选择考评结束时间' ); return;
        }
        if(this.paramData.startTime>=this.paramData.endTime){
          this.vmMsgWarning( '结束时间必须大于开始时间' ); return;
        }
        if(!this.paramData.mode){
          this.vmMsgWarning( '请勾选评教方式' ); return;
        }
        if(Number(this.paramData.max)<0){
          this.vmMsgWarning( '去除最高人数大于等于0' ); return;
        }
        if(Number(this.paramData.min)<0){
          this.vmMsgWarning( '去除最低分人数大于等于0' ); return;
        }
        if(Number(this.paramData.comment)<0){
          this.vmMsgWarning( '评语最少输入字数大于等于0' ); return;
        }
        this.$confirm('是否确定创建该教学评价?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.save=true;
          let startTime = this.paramData.startTime,endTime = this.paramData.endTime;
          if(typeof startTime !== 'string'){
            startTime=formatdata.format(startTime,'yyyy-MM-dd HH:mm');
            endTime=formatdata.format(endTime,'yyyy-MM-dd HH:mm');
          }
          let paramData={
            type:'create',
            semester:this.paramData.semester,
            name:this.paramData.name,
            scope:this.paramData.scope,
            startTime:startTime,
            endTime:endTime,
            mode:this.paramData.mode,
            min:this.paramData.min,
            max:this.paramData.max,
            comment:this.paramData.comment
          };
          req.ajaxSend('/school/StudentEvaluate/createEvaluate','post',paramData,(res)=>{
            this.save=false;
            if(res.status===1){
              this.vmMsgSuccess( res.msg ); return;
            }else {
              this.vmMsgError( res.msg ); return;
            }
          });
        }).catch(() => {

        });
      },
      handleNodeClick(data) {
        if(data.id){
          data.isOpen = !data.isOpen;
        }
        if(!this.scopename.length){
          if(data.id&&data.isOpen){
          }else if(data.classId){
            this.scopename.push(data);
          }
        }else{
          if(data.id&&data.isOpen){
            data.children.forEach(rootVal=>{
              if(this.scopename.some(val=>rootVal.classId===val.classId)){
                return;
              }
            });
          }else if(data.classId){
            if(this.scopename.some(val=>data.classId===val.classId)){
              this.vmMsgWarning( '该班级已选择' ); return;
            }
            this.scopename.push(data);
          }
        }
      },
      handleClose(tag) {
        this.scopename.splice(this.scopename.indexOf(tag), 1);
      },
    }
  }
</script>
<style lang="less">
  .el-tag{
    background-color: #f08bc5;
    margin-left: 1rem;
    color: #ffffff;
    .el-icon-close{
      color: #ffffff;
    }
  }
</style>
<style lang="less" scoped>
  .NewEvaluationTeacher{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;

    .el-tag:first-child{
      margin-left:1rem;
    }
    .Tagsdiv{
      border: 1px solid #bfcbd9;
      min-height: 36px;
      border-radius: 4px;
      line-height:36px;
    }
  }
  .NewEvaluationTeacher  .CreateProcess-top-btn{
    padding:.5rem 2.8rem ;
    border-radius: 1.1rem;
    cursor: pointer;
  }
  .Appset-right{
    border: 1px solid #d2d2d2;
    height:43.5rem;
    margin-top: 2rem;
    border-radius: .4rem;
    overflow-y: auto;
    margin-bottom: 1rem;
    box-shadow: 0 0.1rem 0.1rem 0.12rem rgba(0, 0, 0, 0.09) inset;
  }
  .NewEvaluationTeacher-right{
    height:43.5rem;
    margin-top: 2rem;
    padding-top:3rem;
  }
  .Appset-left-title{
    font-size: 1.1rem;
    padding: 1.6rem 0 1.6rem 1rem;
  }
  .Appset-right-title{
    padding: .8rem 0 .8rem .8rem;
    font-weight: bold;
    font-size: 0.95rem;
  }
  .NewEvaluationTeacher .Appset-border{
    border: 1px solid #d2d2d2;
  }
  .NewEvaluationTeacher .el-tree{
    border: none;
    margin-bottom:3rem;
  }
  .NewEvaluationTeacher-margin-top{
    margin-top: 1.8rem;
  }
  .NewEvaluationTeacher-right-btn{
    width: 100%;
    margin-top:3.8rem;
  }
</style>
