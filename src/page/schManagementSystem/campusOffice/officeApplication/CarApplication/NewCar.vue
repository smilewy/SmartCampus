<template>
  <div class="newDcoument">
    <h3>新建用车申请</h3>
    <el-row class="newDoc-top" type="flex" align="middle">
      <el-col :span="24">
        <el-col :span="11">
          <el-col :span="4" class="newDoc-top-title"><span style="color:red">*</span> 标题：</el-col>
          <el-col :span="20">
            <el-input v-model="param.title"></el-input>
          </el-col>
        </el-col>
        <el-col :span="11" :offset="2">
          <el-col :span="4" class="newDoc-top-title"><span style="color:red">*</span>类型：</el-col>
          <el-col :span="20">
            <el-select style="width: 100%" v-model="param.name" placeholder="请选择">
              <el-option
                v-for="item in options"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </el-col>
        </el-col>
      </el-col>
    </el-row>
    <el-row class="newDoc-top" type="flex" align="middle">
      <el-col :span="24">
        <el-col :span="11">
          <el-col :span="4" class="newDoc-top-title"><span style="color:red">*</span>车辆类型：</el-col>
          <el-col :span="20">
            <el-select style="width: 100%" v-model="param.carType" placeholder="请选择">
              <el-option
                v-for="item in options0"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </el-col>
        </el-col>
        <el-col :span="11" :offset="2">
          <el-col :span="4" class="newDoc-top-title"><span style="color:red">*</span> 用车人数：</el-col>
          <el-col :span="20">
            <el-input-number v-model="param.users" @change="handleChange" :min="1"></el-input-number>
          </el-col>
        </el-col>
      </el-col>
    </el-row>
    <el-row class="newDoc-top" type="flex" align="middle">
      <el-col :span="24">
        <el-col :span="11">
          <el-col :span="4" class="newDoc-top-title"><span style="color:red">*</span> 目的地：</el-col>
          <el-col :span="20">
            <el-input v-model="param.destination" placeholder="请输入目的地"></el-input>
          </el-col>
        </el-col>
        <el-col :span="11" :offset="2">
          <el-col :span="4" class="newDoc-top-title"><span style="color:red">*</span> 用车联系人：</el-col>
          <el-col :span="20">
            <el-input v-model="param.contactMan" placeholder="请输入真实姓名"></el-input>
          </el-col>
        </el-col>
      </el-col>
    </el-row>
    <el-row class="newDoc-top" type="flex" align="middle">
      <el-col :span="24">
        <el-col :span="11">
          <el-col :span="4" class="newDoc-top-title"><span style="color:red">*</span> 联系电话：</el-col>
          <el-col :span="20">
            <el-input v-model="param.telephone" placeholder="请输入用车人联系电话"></el-input>
          </el-col>
        </el-col>
        <el-col :span="11" :offset="2">
          <el-col :span="4" class="newDoc-top-title"><span style="color:red">*</span> 开始时间：</el-col>
          <el-col :span="20">
            <el-date-picker
              type="datetime"
              placeholder="选择时间"
              v-model="param.startTime"
              style="width: 100%"
              format="yyyy-MM-dd HH:mm"
              :picker-options="pickerBeginDateBefore"
              :editable="false"></el-date-picker>
          </el-col>
        </el-col>
      </el-col>
    </el-row>
    <el-row class="newDoc-top" type="flex" align="middle">
      <el-col :span="24">
        <el-col :span="11">
          <el-col :span="4" class="newDoc-top-title"><span style="color:red">*</span> 结束时间：</el-col>
          <el-col :span="20">
            <el-date-picker
              type="datetime"
              placeholder="选择时间"
              v-model="param.endTime"
              style="width: 100%"
              format="yyyy-MM-dd HH:mm"
              :picker-options="pickerBeginDateAfter"
              :editable="false">

            </el-date-picker>
          </el-col>
        </el-col>
        <!--<el-col :span="11" :offset="2">-->
        <!--<el-col :span="4" class="newDoc-top-title">申请时长：</el-col>-->
        <!--<el-col :span="20">-->
        <!--<el-input v-model="param.duration"></el-input>-->
        <!--</el-col>-->
        <!--</el-col>-->
      </el-col>
    </el-row>
    <el-row class="newDoc-top" type="flex" align="middle">
      <el-col :span="24">
        <el-col :span="2" class="newDoc-top-title" style="margin-left: -.8rem"><span style="color:red">*</span> 申请原因：</el-col>
        <el-col :span="22" style="height:18rem">
          <quill-editor style="height:80%" v-model="param.reason"></quill-editor>
        </el-col>
      </el-col>
    </el-row>
    <el-row class="newDoc-top" style="margin:4rem 0 .8rem 0;"  type="flex" align="middle">
      <el-col :span="24">
        <el-col :span="22" class="Car-footer">
          <el-col>公差派车填表说明：</el-col>
          <el-col>1.“用车联系人”需填写真实姓名。</el-col>
          <el-col>2.提交表单前请核实“出发时间”和“返回时间”是否正确。</el-col>
          <el-col>3.请在“事由”一栏填写详细事由，否则可能被拒！</el-col>
          <el-col>4.标红色号*栏目不可留空，否则无法提交。</el-col>
        </el-col>
        <el-col :span="2">
          <el-button type="primary" class="Car-btn" :loading="save===true" @click="PublishnewCar()">发布</el-button>
        </el-col>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  import Vue from 'vue'
  import req from '../../../../../assets/js/common'
  import formatdata from './../../../../../assets/js/date'
  export default{
    data(){
      return {
        save:false,
        options: [{
          value: '',
        }],
        options0: [{
          value: '公车',
        },{
          value: '私车',
        }],
        pickerBeginDateBefore: {
          disabledDate: (time) => {
            if (this.param.endTime) {
              return time.getTime() > this.param.endTime ;
            }
          }
        },
        pickerBeginDateAfter: {
          disabledDate: (time) => {
            if (this.param.startTime) {
              return time.getTime() < this.param.startTime-8.64e7 ;
            }
          }
        },
        param: {
          type:'create',
          title: '',
          name:'',
          carType:'',
          users:'',
          destination:'',
          contactMan:'',
          telephone:'',
          endTime:'',
          startTime:'',
          reason: '',
        }
      }
    },
    created(){
      let param={
        kind:2
      };
      req.ajaxSend('/school/WorkDemand/getName','post',param,(res)=>{
        this.options = res.map(val=>({value:val.name}))
      });
    },
    methods: {
      PublishnewCar(){
        if(!this.param.title){
          this.vmMsgWarning('请输入用车申请标题');
          return;
        }
        if(!this.param.name){
          this.vmMsgWarning('请选择发布类型');
          return;
        }
        if(!this.param.carType){
          this.vmMsgWarning('请选择车辆类型');
          return;
        }
        if(!this.param.users){
          this.vmMsgWarning('请输入用车人数');
          return;
        }
        if(!this.param.destination){
          this.vmMsgWarning('请输入目的地');
          return;
        }
        if(!this.param.contactMan){
          this.vmMsgWarning('请输入用车联系人');
          return;
        }
        if(!(/\d{3}-\d{8}|\d{4}-\{7,8}/.test(this.param.telephone)||/(13\d|14[579]|15[^4\D]|17[^49\D]|18\d)\d{8}/.test(this.param.telephone))){
          this.vmMsgWarning('请正确输入用车人联系电话');
          return;
        }
        if(!this.param.startTime){
          this.vmMsgWarning('请选择用车开始时间');
          return;
        }
        if(!this.param.endTime){
          this.vmMsgWarning('请选择用车结束时间');
          return;
        }
        if(this.param.startTime>this.param.endTime){
          this.vmMsgWarning('结束时间必须大于开始时间');
          return;
        }
        if(!this.param.reason){
          this.vmMsgWarning('请填写用车申请原因');
          return;
        }
        this.$confirm('是否确定新建该用车申请?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.save=true;
          let startTime = this.param.startTime,endTime = this.param.endTime;
          if(typeof startTime !== 'string'){
            startTime=formatdata.format(startTime,'yyyy-MM-dd HH:mm:ss');
            endTime=formatdata.format(endTime,'yyyy-MM-dd HH:mm:ss');
          }
          this.param.duration=((Date.parse(endTime) - Date.parse(startTime))/1000/60/60/24)+1;
          let param={
            type:'create',
            title: this.param.title,
            name: this.param.name,
            carType: this.param.carType,
            users: this.param.users,
            destination: this.param.destination,
            contactMan: this.param.contactMan,
            telephone: this.param.telephone,
            endTime:endTime,
            startTime:startTime,
            reason:  this.param.reason,
          };
          req.ajaxSend('/school/WorkDemand/createCar','post',param,(res)=>{
            this.save=false;
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
            }else {
              this.vmMsgError(res.msg);
            }
          });
        }).catch(() => {

        });
      },
      handleChange(value) {
//        console.log(value);
      }
    }
  }
</script>
<style lang="less" scoped>
  .newDcoument{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .newDoc-top{
    margin: 2rem 0 -1rem 0;
  }
  .newDoc-top-title{
    margin-top: 0.5rem;
    text-align: right;
  }
  .newDoc-text-title{
    font-weight: bold;
    padding-bottom: .8rem;
  }
  @media (max-width: 1600px) {
    .noticeUeditor {
      height: 38.5rem;
    }
  }
  @media (max-width: 1420px) {
    .noticeUeditor {
      height: 37.7rem;
    }
  }
  .Car-footer{
    font-size:14/16rem ;
    color: #999999;
    letter-spacing:.1rem;
  }
  .Car-btn{
    width: 90%;
    margin-top:4rem;
  }
</style>
