<template>
  <div class="NewfieldDetails">
    <el-col :span="24">
      <el-button type="primary" @click="returnBack()"
                 style="background-color: #FE8687;border-color: #FE8687;border-radius: 1.2rem;padding: .43rem 1.6rem;">
        <img src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png">
        <span>返回</span>
      </el-button>
    </el-col>
    <el-col :span="24" style="margin-top: 2rem">
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
    <el-col :span="24" style="margin-top: 1.2rem">
      <el-col :span="11">
        <el-col :span="4" class="newDoc-top-title"> 使用场地：</el-col>
        <el-col :span="20">
          <el-input v-model="name" disabled></el-input>
        </el-col>
      </el-col>
      <el-col :span="11" :offset="2">
        <el-col :span="4" class="newDoc-top-title">详细地址：</el-col>
        <el-col :span="20">
          <el-input v-model="param.address" placeholder="请输入具体详细地址"></el-input>
        </el-col>
      </el-col>
    </el-col>
    <el-col :span="24" style="margin-top: 1.2rem" :key="index" v-for="(item,index) in useData">
      <el-col :span="6">
        <el-col :span="8" class="newDoc-top-title" style="margin-left: -.6rem"> 使用日期：</el-col>
        <el-col :span="16">
          <el-date-picker
            type="date"
            v-model="item.date"
            :editable="false"
            placeholder="选择日期时间"
            :picker-options="pickerOptions0">
          </el-date-picker>
        </el-col>
      </el-col>
      <el-col :span="14" :offset="1">
        <el-col :span="3" class="newDoc-top-title">使用时间：</el-col>
        <el-col :span="8" :offset="1">
          <el-time-picker
            is-range
            style="width: 100%"
            value-format="HH:mm"
            :editable="false"
            v-model="item.time"
            range-separator="至"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            placeholder="选择时间范围">
          </el-time-picker>
        </el-col>
        <el-col :span="8" :offset="1">
          <span class="Occupation-time" @click="ViewoccupancyTime(item)">查看已占用的时间</span>
          <span class="NewfieldDetails-add2" v-if="useData.length-1===index" @click="addDate(useData)">+</span>
          <span class="NewfieldDetails-add2 NewfieldDetails-add3" v-if="index!==0&&useData.length-1===index" @click="delDate(useData,index)">-</span>
        </el-col>
      </el-col>
    </el-col>
    <el-col :span="24" style="margin-top: 1.2rem">
      <el-col :span="11">
        <el-col :span="4" class="newDoc-top-title"><span style="color:red">*</span> 负责人：</el-col>
        <el-col :span="20">
          <el-input v-model="param.principal"></el-input>
        </el-col>
      </el-col>
      <el-col :span="11" :offset="2">
        <el-col :span="4" class="newDoc-top-title"><span style="color:red">*</span>联系方式：</el-col>
        <el-col :span="20">
          <el-input v-model="param.telephone" placeholder="请输入联系方式"></el-input>
        </el-col>
      </el-col>
    </el-col>
    <el-col :span="24" style="margin-top: 1.2rem">
      <el-col :span="11">
        <el-col :span="4" class="newDoc-top-title"> 配置选择：</el-col>
        <el-col :span="20">
          <el-col :span="1" :offset="1">
            <span class="NewfieldDetails-add2" @click="Showdialog()">+</span>
          </el-col>
          <el-col :span="21" :offset="1" style="padding-top: .3rem;">
            <p class="NewfieldDetails-option">已选：<span v-for="item in setopname">{{item}}</span></p>
          </el-col>
        </el-col>
      </el-col>
      <el-col :span="11">
        <p class="Occupation-time2" @click="viewMore()">查看更多</p>
      </el-col>
    </el-col>
    <el-col :span="24" style="margin-top: 1.2rem">
      <el-col :span="2" class="newDoc-top-title" style="margin-left: -.6rem"><span style="color:red">*</span> 说明：</el-col>
      <el-col :span="22">
        <textarea type="text" v-model="param.explain" placeholder="请输入LED电子屏标语或备注内容，没有内容可填写“无”" class="NewfieldDetails-reason"></textarea>
      </el-col>
    </el-col>
    <el-col :span="24"  style="margin: 4rem 0 3rem 0">
      <el-col :span="22" class="Car-footer">
        <el-col>场地申请填表说明：</el-col>
        <el-col>1. 请至少提前12小时上网预约场地准备。如遇急用，请上网预约后致电审批人。</el-col>
        <el-col>2.“场地负责人”一栏请填写您的真实姓名。</el-col>
        <el-col>3. 提交表单前请核实“使用场地”和“使用时间”是否正确，以免造成延误。</el-col>
        <el-col>4. 标红色* 号栏目不可留空，否则无法提交。</el-col>
      </el-col>
      <el-col :span="2">
        <el-button  type="primary" class="Car-btn" :loading="save===true" @click="PublishNewField()">发布</el-button>
      </el-col>
    </el-col >
    <el-dialog title="选择配置" :visible.sync="dialogTableVisible" :modal="false">
      <el-row>
        <el-table
          :data="tableData"
          style="width: 100%;height: 28rem;overflow-y: auto;">
          <el-table-column
            type="index"
            label="序号"
            width="66">
          </el-table-column>
          <el-table-column
            prop="name"
            label="名称"
            align="center">
          </el-table-column>
          <el-table-column
            prop="option"
            label="选项"
            align="center"
            width="500">
            <template  slot-scope="scope">
              <el-checkbox-group v-model="scope.row.selectedVal">
                <el-checkbox :label="val" :key="index" v-for="(val,index) in scope.row.option">{{val}}</el-checkbox>
              </el-checkbox-group>
            </template>
          </el-table-column>
        </el-table>
        <el-col :span="1" :offset="11">
          <el-button type="primary" style="padding:.6rem 2.5rem;border-radius:1.1rem;margin: 2rem 0;" @click="Savesetoption()">确定</el-button>
        </el-col>
      </el-row>
    </el-dialog>
    <el-dialog title="查看已占用时间" :visible.sync="Viewoccupancy" :modal="false">
      <!--<div style="height:28rem;overflow-y: auto">-->
      <!--<p style="text-align: center;padding:1.6rem 0;" v-for="item in dialogData">{{item}}</p>-->
      <!--</div>-->
      <el-row>
        <el-table
          :data="dialogData"
          style="width: 100%;height: 26rem;overflow-y: auto">
          <el-table-column
            type="index"
            label="序号"
            width="100"
            align="center">
          </el-table-column>
          <el-table-column
            prop="name"
            label="占用时间"
            align="center">
            <template slot-scope="scope">
              <span>{{scope.row}}</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
    </el-dialog>
    <el-dialog title="查看已选择配置" :visible.sync="Viewoption" :modal="false">
      <el-row>
        <el-table
          :data="setopname"
          style="width: 100%;height: 26rem;overflow-y: auto">
          <el-table-column
            type="index"
            label="序号"
            width="80"
            align="center">
          </el-table-column>
          <el-table-column
            prop="name"
            label="已选配置"
            align="center">
            <template slot-scope="scope">
              <span>{{scope.row}}</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <!--<div style="height:28rem;overflow-y: auto">-->
      <!--<p style="text-align: center;padding:1.6rem 0;" v-for="item in setopname">{{item}}</p>-->
      <!--</div>-->
    </el-dialog>
  </div>
</template>
<script>
  import req from '../../../../../assets/js/common'
  import formatdata from './../../../../../assets/js/date'
  export default{
    data(){
      return{
//        id:JSON.parse(sessionStorage.getItem('FileId')).id,
        name:this.$route.params.name,
        id:this.$route.params.id,
        occupyTime:this.$route.params.occupyTime||[],
        save:false,
        setname:'',
        setoption:[],
        setopname:[],
        selected:'',
        useData:[{
          date:'',
          time:['','']
        }],
        options:[],
        tableData: [],
        dialogData:[],
        dialogTableVisible: false,
        Viewoccupancy:false,
        Viewoption:false,
        pickerOptions0: {
          disabledDate(time) {
            return time.getTime() < Date.now() - 8.64e7;
          }
        },
        useTime: [],
        param: {
          type:'create',
          title: '',
          name:'',
          address:'',
          principal:'',
          telephone:'',
          explain:'',
          id:'',
          outfit:[],//数组
          occupyTime:[]//数组
        }
      }
    },
    created(){
      let param={
        kind:3
      };
      req.ajaxSend('/school/WorkDemand/getName','post',param,(res)=>{
        this.options = res.map(val=>({value:val.name}))
      });
    },
    methods:{
      PublishNewField(){
        let occuptied = false,
          requests = [];
        if(this.useData.length){
          this.useData.forEach(val=>{
              if(val.date!==''){
                requests.push(new Promise((resolve) => {
                  req.ajaxSend('/school/WorkDemand/createPlace','post',{
                    type:'look',
                    date:formatdata.format(val.date,'yyyy-MM-dd'),
                    id:this.id
                  },(res)=>{resolve(res);})
                }))
              }
          });
        }
        Promise.all(requests).then(reses=>{
          reses.forEach((val,idx)=>{
            if(val.data){
              val.data.forEach(subVal=>{
                if(!(this.useData[idx].time[0]>=subVal.split('-')[1]||this.useData[idx].time[1]<=subVal.split('-')[0])){
                  occuptied = true;
                }
              });
            }
          });
          if(occuptied){
            this.vmMsgWarning('该时间段已被占用');
            return;
          }
          if(!this.param.title){
            this.vmMsgWarning('请输入场地申请标题');
            return;
          }
          if(!this.param.name){
            this.vmMsgWarning('请选择发布类型');
            return;
          }
          if(!this.param.principal){
            this.vmMsgWarning('请输入负责人');
            return;
          }
          if(!(/\d{3}-\d{8}|\d{4}-\{7,8}/.test(this.param.telephone)||/(13\d|14[579]|15[^4\D]|17[^49\D]|18\d)\d{8}/.test(this.param.telephone))){
            this.vmMsgWarning('请正确输入联系方式');
            return;
          }
          this.param.outfit=this.setopname;
          this.useData.forEach(val=>{
            if(val.time[0]>val.time[1]){
              this.vmMsgWarning('开始时间不能大于结束时间');
            }
            return;
          });
          if(!this.param.explain){
            this.vmMsgWarning('请输入使用说明');
            return;
          }
          this.$confirm('是否确定新建该场地申请?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            this.save=true;
            this.param.occupyTime = [];
            this.useData.forEach(val=>{
              let occupyTime= formatdata.format(val.date,'yyyy-MM-dd')+' '+val.time[0]+'-'+val.time[1];
              this.param.occupyTime.push(occupyTime);
            });
            this.param.id=this.id;
            req.ajaxSend('/school/WorkDemand/createPlace','post',this.param,(res)=>{
              this.save=false;
              if(res.status===1){
                this.vmMsgSuccess(res.msg);
              }else {
                this.vmMsgError(res.msg);
              }
            });
          }).catch((err) => {
          });
        });
      },
      ViewoccupancyTime(data){
        if(!(data.date)){
          this.vmMsgWarning('请选择使用日期');
          return;
        }
        let usedate= formatdata.format(data.date,'yyyy-MM-dd');
        let param={
          type:'look',
          date:usedate,
          id:this.id
        };
        req.ajaxSend('/school/WorkDemand/createPlace','post',param,(res)=>{
          if(res.data){
            this.dialogData=res.data;
          }
        });
        this.Viewoccupancy=true;
      },
      viewMore(){
        this.Viewoption=true;
      },
      Showdialog(){
        req.ajaxSend('/school/WorkDemand/placeOutfit','post',{},(res)=>{
          res.data.forEach(val=>{
            val.selectedVal =[];
          });
          this.tableData=res.data;
        });
        this.dialogTableVisible=true;
      },
      Savesetoption(){
        this.setopname=[];
        this.tableData.forEach(val=>{
          this.setoption=val.selectedVal;
          this.setname=val.name;
          if(this.setoption.length){
            this.setopname.push(this.setname+'('+this.setoption+')');
          }
        });
        this.dialogTableVisible=false;
      },
      returnBack(){
        this.$router.push("/NewfieldHome")
      },
      addDate(list){
        list.push({
          date:'',
          time:[]
        })
      },
      delDate(list,index){
        this.$confirm('确认删除该时间段?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(()=>{
          list.splice(index,1);
        }).catch((err) => {});
      }
    }
  }
</script>
<style lang="less" scoped>
  .newDoc-top-title{
    margin-top: 0.5rem;
    text-align: right;
  }
  .NewfieldDetails .NewfieldDetails-option{
    width: 100%;
    overflow: hidden;
    text-overflow:ellipsis;
    white-space: nowrap;
    span{
      padding: 0 1rem;
    }
  }
  .NewfieldDetails .Occupation-time,.NewfieldDetails .Occupation-time2{
    color:#62A8F6;
    border-bottom: 1px solid #62A8F6;
    cursor: pointer;
    margin-top: .6rem;
    display: inline-block;
  }
  .NewfieldDetails .Occupation-time2{
    margin-top:0;
  }
  .NewfieldDetails-reason{
    width: 100%;
    padding:1rem;
    height: 12rem;
    outline:none;
    border-color: #BFCBD9;
    border-radius: .55rem;
    resize: none;
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
  .NewfieldDetails-add2{
    border: 1px solid #9ACAFD;
    display: inline-block;
    color: #9ACAFD;
    width:1.6rem;
    height:1.6rem;
    line-height:1.6rem;
    text-align: center;
    vertical-align: middle;
    cursor: pointer;
    font-weight: bold;
    font-size: 1rem;
    border-radius: .3rem;
  }
  .NewfieldDetails-add3{
    border: 1px solid #F5965A;
    color: #F5965A;
  }
</style>
