<template>
  <div class="NewLeave">
    <el-row>
      <el-col :span="24">
        <h3>新建请假</h3>
      </el-col>
      <el-col :span="16" :offset="4">
        <div class="new-leave-content">
          <!--请假标题-->
          <el-col :span="24">
            <el-col :span="3">
              请假标题：
            </el-col>
            <el-col :span="21">
              <el-input v-model="inputTitle"></el-input>
            </el-col>
          </el-col>
          <!--请假类型-->
          <el-col :span="24">
            <el-col :span="3">
              请假类型：
            </el-col>
            <el-col :span="21">
              <el-select v-model="Typevalue" placeholder="请选择" style="width: 100%">
                <el-option
                  v-for="item in options"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
            </el-col>
          </el-col>
          <!--起始时间-->
          <el-col :span="24">
            <el-col :span="3">起始时间：</el-col>
            <el-col :span="8">
              <el-date-picker
                v-model="startdata" :editable="false" type="date" :picker-options="pickerOptions0" style="width: 100%">
              </el-date-picker>
            </el-col>
            <el-col :span="2"  style="text-align: center">_</el-col>
            <el-col :span="8">
              <el-time-picker :editable="false" v-model="startTime" format="HH:mm" style="width: 100%"></el-time-picker>
            </el-col>
          </el-col>
          <!--结束时间-->
          <el-col :span="24">
            <el-col :span="3">结束时间：</el-col>
            <el-col :span="8">
              <el-date-picker
                v-model="enddata" type="date" :editable="false" :picker-options="pickerOptions1" style="width: 100%">
              </el-date-picker>
            </el-col>
            <el-col :span="2"  style="text-align: center">_</el-col>
            <el-col :span="8">
              <el-time-picker :editable="false" v-model="endTime" format="HH:mm" style="width: 100%"></el-time-picker>
            </el-col>
          </el-col>
          <!--请假原因-->
          <el-col :span="24">
            <el-col :span="3">
              请假原因：
            </el-col>
            <el-col :span="21" class="new-leave-Reasons">
              <span v-for="(tag,index) in dynamicTags">
                <span @click="ChooseTag(index)">{{tag}}</span>
              </span>
            </el-col>
          </el-col>
          <!--请假原因框-->
          <el-col :span="21" :offset="3">
            <div class="ReasonsText">
              <!--tags标签-->
              <el-col :span="24">
                <el-tag
                  :key="tag"
                  v-for="tag in selectTags"
                  :closable="true"
                  :close-transition="false"
                  @close="handleClose(tag)"
                >
                  {{tag}}
                </el-tag>
              </el-col>
              <el-col :span="24">
                <textarea type="text" v-model="Reasons" placeholder="请输入请假原因" style="font-size:1rem;color:#606266; width: 100%;margin-top:1rem;height: 10rem;border: none;outline:none;resize : none;"></textarea>
              </el-col>
            </div>
          </el-col>
          <el-col :span="7" :offset="17">
            <el-col :span="12">
              <el-button type="primary" class="NewLeave-btn1" :loading="save===true" @click="SaveMsg()">保存</el-button>
            </el-col>
            <el-col :span="12">
              <el-button class="NewLeave-btn2" @click="Refreshpage()">重置</el-button>
            </el-col>
          </el-col>
        </div>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  import req from './../../../../assets/js/common'
  import formatdata from './../../../../assets/js/date'
  export default{
    data(){
      return {
        inputTitle: '',
        options: [
          {
            value: ''
          }],
        dynamicTags: ['因病请假', '因事请假', '其他'],
        selectTags: [],
        Typevalue: '',
        pickerOptions0: {
          disabledDate:(time)=> {
            if(this.enddata){
              return time.getTime() > this.enddata;
            }
          }
        },
        pickerOptions1: {
          disabledDate:(time)=> {
            if(this.startdata){
              return time.getTime() < this.startdata;
            }
          }
        },
        startdata:'',
        enddata:'',
        startTime:'',
        endTime:'',
        Reasons:'',
        tagreson:'',
        save:false
      }
    },
    created(){
      req.ajaxSend('/school/TeacherLeave/getName','post',{},(res)=>{
        this.options = res.map(val=>({value:val.name}))
      });
    },
    methods: {
      Refreshpage(){
        this.inputTitle='';
        this.Typevalue='';
        this.Reasons='';
        this.startdata='';
        this.enddata='';
        this.startTime='';
        this.endTime='';
        this.selectTags=[];
      },
      SaveMsg(){
        if(!this.inputTitle){
          this.vmMsgWarning('请输入请假标题');
          return;
        }
        if(!this.Typevalue){
          this.vmMsgWarning('请选择请假类型');
          return;
        }
        if(!this.startdata){
          this.vmMsgWarning('请选择开始日期');
          return;
        }
        if(!this.startTime){
          this.vmMsgWarning('请选择开始时间');
          return;
        }
        if(!this.enddata){
          this.vmMsgWarning('请选择结束日期');
          return;
        }
        if(!this.endTime){
          this.vmMsgWarning('请选择结束时间');
          return;
        }
        if(typeof this.startdata !== 'string' ||typeof this.startTime !== 'string'){
          if(formatdata.format(this.startdata,'yyyy-MM-dd')==formatdata.format(this.enddata,'yyyy-MM-dd') && formatdata.format(this.startTime,'HH:mm')>formatdata.format(this.endTime,'HH:mm')){
            this.vmMsgWarning('结束时间必须大于开始时间');
            return;
          }
        }
        if(this.selectTags.length>1){
          this.vmMsgWarning('请假原因标签最多只能选择一个');
          return;
        }
        if(!(this.Reasons||this.selectTags.length)){
          this.vmMsgWarning('请输入请假原因');
          return;
        }
        this.selectTags.forEach(val=>{
          if(val){
            this.tagreson='【'+val+'】';
          }
        });
        this.$confirm('是否确认新建该请假?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.save=true;
          let startdata = this.startdata,enddata = this.enddata,startTime = this.startTime,endTime = this.endTime;
          if(typeof startdata !== 'string' ||typeof startTime !== 'string'){
            startdata=formatdata.format(startdata,'yyyy-MM-dd');
            enddata=formatdata.format(enddata,'yyyy-MM-dd');
            startTime=formatdata.format(startTime,'HH:mm');
            endTime=formatdata.format(endTime,'HH:mm');
          }
          let param={
            type:'create',
            title:this.inputTitle,
            name:this.Typevalue,
            startTime:startdata+' '+startTime,
            endTime:enddata+' '+endTime,
            reason:this.tagreson+this.Reasons
          };
          req.ajaxSend('/school/TeacherLeave/create','post',param,(res)=>{
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
      ChooseTag(idx){
        this.selectTags.push(this.dynamicTags[idx])
      },
      handleClose(tag) {
        this.selectTags.splice(this.selectTags.indexOf(tag), 1);
      },
    }
  }
</script>
<style lang="less" scoped>
  .NewLeave{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .new-leave-content{
    margin-top: 3.75rem;
    min-height: 43.5rem;
  }
  .new-leave-content>div{
    margin-bottom: 1.875rem;
  }
  .new-leave-Reasons >span>span{
    display: inline-block;
    color:#f08bc5;
    border: 1px solid #f08bc5;
    background-color: #ffffff;
    height: 1.65rem;
    line-height: 1.65rem;
    width:5.4rem;
    text-align: center;
    margin-right:1.25rem;
    cursor: pointer;
    font-size:14px;
    border-radius: 4px;
  }
  .ReasonsText{
    height:12.75rem;
    border: 1px solid #d2d2d2;
    border-radius: 0.5rem;
    width: 54rem;
    padding: 1.5rem;
  }
  .NewLeave-btn1{
    background: #4da1ff;
    width: 7.6rem;
    border-radius: 1.1rem;
  }
  .NewLeave-btn2{
    color: #4da1ff;
    width: 7.6rem;
    border-radius: 1.1rem;
    border: 1px solid #4da1ff;
  }
  .NewLeave-tags{
    width: 7rem;
    height: 2.4rem;
    line-height: 2.4rem;
    background-color: #f08bc5;
    text-align: center;
  }
</style>
<style>
  .el-tag{
    color:#ffffff;
    border: 1px solid #f08bc5;
    background-color: #f08bc5;
    margin-right:1.25rem;
  }
  .el-tag .el-icon-close{
    color:#ffffff;
  }
</style>
