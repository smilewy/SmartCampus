<template>
  <div class="TeachingEvaluationStudents">
    <h3>学生评价教师</h3>
    <el-row style="margin-top: 2rem;">
      <el-col :span="24" class="Infor-head" style="border-bottom: 1px solid #d2d2d2;">
        <el-col :span="22">
          <el-form :inline="true" :model="form" class="demo-form-inline Infor-title clear_fix">
            <el-form-item label="评教名称：">
              <el-select v-model="form.name" placeholder="请选择评教名称">
                <el-option
                  v-for="item in nameoptions"
                  :key="item.id"
                  :label="item.name"
                  :value="item.id">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="评教时间:">
              <span v-if="showTime.startTime">
                {{showTime.startTime}} 至 {{showTime.endTime}}
              </span>
            </el-form-item>
          </el-form>
        </el-col>
        <el-col :span="2">
          <el-button type="primary" style="padding-left:1.48rem;padding-right:1.48rem;" :disabled="joined===1" @click="SaveMsg()">保存</el-button>
        </el-col>
      </el-col>
    </el-row>
    <el-col :span="24" style="padding-bottom: 30px"
            v-loading.body="isLoading"
            element-loading-text="拼命加载中...">
      <el-col :span="8" class="Maincontent" v-for="(data,idx) in ContentData" :key="data.title">
        <div class="content">
          <div class="title">{{data.name}}({{data.subject}})</div>
          <el-row>
            <el-col :span="24" class="text">
              <el-col :span="3">评分：</el-col>
              <el-col v-if="showTime.mode==='1'" :span="21">
                <el-input v-model="data.value" type="number"></el-input>
              </el-col>
              <el-col v-if="showTime.mode==='2'" :span="21">
                <span v-for="(item,index) in satisfactions"
                      :class="{ active:suitSatisfaction(data.property,index)}"
                      @click="changeSatisfaction(idx,index)"
                      class="scoreTags">{{item}}
                </span>
              </el-col>
              <el-col v-if="showTime.mode==='3'" :span="21">
                <el-rate v-model="data.property" :colors="['#F08BC5', '#F08BC5', '#F08BC5']"></el-rate>
              </el-col>
            </el-col>
          </el-row>
          <el-row>
            <el-col :span="24" class="text">
              <el-col :span="3">评语：</el-col>
              <el-col :span="21">
                <textarea class="myText" v-model="data.remark" :placeholder="tips"></textarea>
              </el-col>
            </el-col>
          </el-row>
        </div>
      </el-col>
      <el-col :span="24" v-if="!this.form.name">
        <p style="padding: 150px 0 300px;text-align: center">请选择评价名称</p>
      </el-col>
    </el-col>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import formatdata from '@/assets/js/date'
  export default{
    data(){
      return {
        form: {
          name: '',
        },
        isLoading:false,
        nameoptions:[],
        showTime:{},
        ContentData:[],
        satisfactions:[],
        recordId:[],
        maxscore:'',
        comment:'',
        joined:0,
      }
    },
    created(){
      this.getName();
    },
    computed:{
        tips(){
            return "请从教师的优点、缺点、改进意见来进行评价" + ( this.comment ? "，字数不低于" + this.comment + "个字" : "" );
        }
    },
    watch:{
      'form.name':function(val){
        this.ChooseTime(val)
      }
    },
    methods:{
      getList(){
        req.ajaxSend('/school/StudentEvaluate/studentMark','post',{evaId:this.showTime.id},(res)=>{
          res.data.forEach(val=>{
            if(val.mode==='3'){
              if(typeof val.property === 'string'){
                val.property = parseInt(val.property.slice(1));
              }else if(typeof val.property !== 'number'){
                val.property = 0;
              }
            }
            this.recordId.push(val.recordId);
          });
          this.joined = res.joined;
          this.ContentData = res.data;
        });
      },
      getName(){
        this.isLoading=true;
        req.ajaxSend('/school/StudentEvaluate/common','post',{func:'getBelongEvaluate'},(res)=>{
          this.nameoptions=res.data;
          if( res.data.length > 0 ){
              this.form.name= res.data[0].id;
          }
          this.isLoading=false;
        });
      },
      suitSatisfaction(level,index){
        return level.slice(1)-1 === index;
      },
      SaveMsg(){
        if(!this.form.name){
          this.vmMsgWarning( '请选择评教名称' ); return;
        }
        if(this.joined){
          this.vmMsgWarning( '已评教，不可二次修改' ); return;
        }
        let records = JSON.parse(JSON.stringify(this.ContentData));
        if(records.some(val=>!(val.value || val.property) )){
          this.vmMsgWarning( '请填写评分' ); return;
        }
        if(records.some(val=>val.remark.length<this.comment)){
          this.vmMsgWarning( '评语至少填写'+this.comment+'个字' ); return;
        }
        if(records.some(val=>val.value > parseInt(this.maxscore))){
          this.vmMsgWarning( '分数不能超过'+this.maxscore+'分' ); return;
        }
        records.forEach(val=>{
          if(val.mode==='1'){
            val.property='score';
          }
          if(val.mode!=='1'){
            val.value=1;
          }
          if(val.mode==='3'){
            val.property = 's'+ val.property;
          }
        });
        this.$confirm('是否确定保存该教学评价?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          let param={
            type:'submit',
            evaId:this.form.name,
            recordId:this.recordId,
            record:records
          };
          req.ajaxSend('/school/StudentEvaluate/studentMark','post',param,(res)=>{
            if(res.status===1){
              this.joined=1;
              this.vmMsgSuccess( res.msg );
            }else {
              this.vmMsgError( res.msg );
            }
          });
        }).catch(() => {
        });
      },
      ChooseTime(){
        this.recordId = [];
        this.ContentData = [];
        for (let obj of this.nameoptions) {
          if (obj.id === this.form.name) {
            this.showTime=obj;
            if(/^\d+$/.test(obj.startTime)){
             let startTime=formatdata.format(new Date(obj.startTime*1000),'yyyy-MM-dd HH:mm'),endTime=formatdata.format(new Date(obj.endTime*1000),'yyyy-MM-dd HH:mm');
             this.showTime.startTime=startTime;this.showTime.endTime=endTime;
           }
            this.satisfactions=obj.field;
            this.maxscore=obj.score;
            this.comment=obj.comment;
            this.getList();
          }
        }
      },
      changeSatisfaction(index,idx){
        this.ContentData[index].property = `f${idx+1}`;
      },
    }
  }
</script>
<style lang="less" scoped>
  .TeachingEvaluationStudents{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    .Maincontent{
      padding-right:1.2rem;
      padding-left: .6rem;
    }
    /*.el-button.is-disabled, .el-button.is-disabled:focus, .el-button.is-disabled:hover{*/
      /*color: #ffffff;*/
      /*cursor: not-allowed;*/
      /*background-image: none;*/
      /*background-color: #20a0ff;*/
      /*border-color:#20a0ff;*/
    /*}*/
    .content{
      border: 1px solid #d2d2d2;
      margin-top: 1.8rem;
      border-radius: 1rem;
      height:19.5rem;
    }
    .title{
      border-bottom: 1px solid #d2d2d2;
      padding: .6rem;
      font-weight: bold;
    }
    .text{
      padding: 1rem;
    }
    .scoreTags{
      display: inline-block;
      background-color: #B1B1B1;
      color: #fff;
      margin-left: .5rem;
      padding: .5rem 1.1rem;
      border-radius: .38rem;
      font-size:.9rem;
      margin-bottom: .5rem;
      cursor: pointer;
    }
    .active{
      background: #89BCF5;
    }
    .myText{
      width:96%;
      height:6rem;
      border-radius: .35rem;
      resize: none;
      padding: .8rem .3rem;
      color: #999999;
    }
  }
</style>
<style>
 .TeachingEvaluationStudents .el-tag{
    background-color:#89BCF5 ;
    margin-left:1.8rem;
    margin-top: 1.6rem;
    padding: .3rem .6rem;
    height: 100%;
    color: #ffffff;
  }
 .TeachingEvaluationStudents .el-tag .el-icon-close{
    color: #FFFFFF;
  }
</style>
