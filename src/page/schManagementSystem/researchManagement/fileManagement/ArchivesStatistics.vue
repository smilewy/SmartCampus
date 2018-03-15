<template>
  <div class="ArchivesStatistics">
    <h3>档案统计</h3>
    <el-row class="LeaveRecord-title" type="flex" align="center">
      <el-form :inline="true" class="formInline">
        <el-form-item label="标签：">
          <el-input v-model="optionsvalue" @focus="Showcheckbox()"></el-input>
          <!--多选框-->
          <el-col :span="24" class="FilerecordPassed-checkbox" v-if="showcheckbox">
            <el-col :span="24" :key="row.id" v-for="row in Alltags">
              <el-checkbox :indeterminate="row.hasChecked" v-model="row.allChecked" @change="selectAll(row)">{{row.name}}</el-checkbox>
              <div>
                <el-checkbox v-for="tag in row.tags"  @change="checkTag(row)" :label="tag.name" :key="tag.id" v-model="tag.checked">{{tag.name}}</el-checkbox>
              </div>
            </el-col>
            <el-col :span="1" :offset="9">
              <el-button type="primary"  class="Hidecheckbox-btn" @click ="Hidecheckbox()">确定</el-button>
            </el-col>
          </el-col>
        </el-form-item>
        <el-form-item label="档案时间：">
          <el-date-picker
            v-model="startvalue" type="date" :picker-options="pickerOptions0" style="width: 100%">
          </el-date-picker>
        </el-form-item>
        <span>_</span>
        <el-form-item>
          <el-date-picker
            v-model="endvalue" type="date" :picker-options="pickerOptions1" style="width: 100%">
          </el-date-picker>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row type="flex" align="center">
      <el-form :inline="true" class="formInline">
        <el-form-item label="维度：">
          <el-select v-model="selectvalue0" placeholder="请选择">
            <el-option
              v-for="item in options0"
              :key="item.id"
              :label="item.name"
              :value="item.id">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="维度：">
          <el-select v-model="selectvalue1" placeholder="请选择">
            <el-option
              v-for="item in options1"
              :key="item.id"
              :label="item.name"
              :value="item.id">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" icon="el-icon-search" class="LeaveRecord-search" @click="GetcountList">查询</el-button>
        </el-form-item>
      </el-form>
    </el-row>
    <el-col :span="24" style="margin-top: 2rem;" :style="{opacity:tableData.length?1:0}">
      <el-row class="StaticsList">
        <el-table
          :data="tableData"
          style="width: 100%"
          border
          v-loading.body="isLoading"
          element-loading-text="拼命加载中...">
          <el-table-column
            prop="name"
            label="标签"
            align="center">
          </el-table-column>
          <el-table-column
            v-for="colume in columes"
            :key="colume.prop"
            :label="colume.label"
            align="center">
            <template slot-scope="scope">
              <span>{{scope.row[colume.prop]}}</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-row style="margin-top: 2rem;">
        <div id="staticEchart"></div>
      </el-row>
    </el-col>
  </div>
</template>
<script>
  import echarts from 'echarts'
  import req from '../../../../assets/js/common'
  import formatdata from '../../../../assets/js/date'
  export default{
    data(){
      return {
        pickerOptions0: {
          disabledDate:(time)=> {
            if(this.endvalue){
              return time.getTime() > this.endvalue;
            }
          }
        },
        pickerOptions1: {
          disabledDate:(time)=> {
            if(this.startvalue){
              return time.getTime() < this.startvalue;
            }
          }
        },
        startvalue:'',
        endvalue:'',
        optionsvalue: '',
        Alltags:[],
        inTags:[],
        columes:[],
        xAxisData:[],
        legendData:[],
        seriesData:[],
        showcheckbox: false,
        selectvalue0: '',
        selectvalue1: '',
        options0: [],
        options1: [],
        tableData:[],
        isLoading:false,
        chartDom:null
      }
    },
    created(){
      this.getallTag();
    },
    mounted(){
      this.chartDom = echarts.init(document.getElementById('staticEchart'));
    },
    methods:{
      drawChart(){
        let colors=['#89BCF5','#f08bc5'];
        let option= {
          tooltip : {
            trigger: 'axis',
            axisPointer : {            // 坐标轴指示器，坐标轴触发有效
              type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
          },
          legend: {
            data :this.legendData
          },
          color:colors,
          grid: {
            left: '3%',
            right: '3%',
            bottom: '16%',
            containLabel: true,
            show: true,
            borderWidth:'30px'
          },
          xAxis : [
            {
              type : 'category',
              data :this.xAxisData
            }
          ],
          yAxis : [
            {
              type : 'value'
            }
          ],
          series :this.seriesData
        };
        this.chartDom.setOption(option,true);
        window.onresize = this.chartDom.resize;
      },
      GetcountList(){
        if(!this.optionsvalue){
          this.vmMsgWarning( '请选择标签！' ); return;
        }
        if(!this.startvalue){
          this.vmMsgWarning( '请选择开始时间！' ); return;
        }
        if(!this.endvalue){
          this.vmMsgWarning( '请选择结束时间！' ); return;
        }
        if(!(this.selectvalue0 ||this.selectvalue1)){
          this.vmMsgWarning( '请选择维度！' ); return;
        }
        if(this.selectvalue0===this.selectvalue1){
          this.vmMsgWarning( '两个维度不能相同！' ); return;
        }
        let
          startvalue=formatdata.format(this.startvalue,'yyyy-MM-dd')+' 00:00:00',
          endvalue=formatdata.format(this.endvalue,'yyyy-MM-dd')+' 23:59:59',
          param={
            tag:[],
            startTime:startvalue,
            endTime:endvalue,
          };
        if(this.selectvalue0){
          let vds  = this.options0.filter(val=>val.id===this.selectvalue0)[0].tags.filter(val=>val.checked).map(val=>val.id);
          param.tag.push(vds);
        }
        if(this.selectvalue1){
          let vds  = this.options1.filter(val=>val.id===this.selectvalue1)[0].tags.filter(val=>val.checked).map(val=>val.id);
          param.tag.push(vds);
        }
        this.xAxisData=[];
        this.legendData=[];
        this.tableData=[];
        req.ajaxSend('/school/FileManage/fileStatistics','post',param,(res)=>{
          this.seriesData = res.filed.map((val)=>{
            let obj = {
              type:'bar',
              stack:'1',
              barWidth:'40px',
              name:val.name,
              data:[]
            };
            res.data.forEach((subVal)=>{
              obj.data.push(subVal[val.id]);
            });
            return obj;
          });
          this.columes = res.filed.map(val=>{
            this.legendData.push(val.name);
            return {
              label:val.name,
              prop:val.id
            };
          });
          res.data.forEach(val=>{
            this.xAxisData.push(val.name);
          });
          this.tableData=res.data;
          this.drawChart();
        });
      },
      Showcheckbox(){
        this.showcheckbox=true;
      },
      Hidecheckbox(){
        this.selectvalue0= '';
        this.selectvalue1= '';
        this.optionsvalue = '';
        this.inTags = [];
        let vd1 = [];
        this.Alltags.forEach(rootVal=>{
          if(!rootVal.tagsChecked.length)
            return;
          this.optionsvalue += (rootVal.name+'，'+rootVal.tagsChecked.map(val=>val.name).join('、')+'，');
          this.inTags = this.inTags.concat(rootVal.tagsChecked.map(val=>val.id));
          if(rootVal.hasChecked||rootVal.allChecked){
            let val_temp = this.deepCopy(rootVal);
            val_temp.tags = val_temp.tags.filter(val=>val.checked);
            vd1.push(val_temp);
          }
        });
        this.options0 = vd1;
        this.options1 = vd1;
        this.showcheckbox=false;
      },
      deepCopy(data){return JSON.parse(JSON.stringify(data))},
      getallTag(){
        req.ajaxSend('/school/FileManage/common','post',{func:'getTag'},(res)=>{
          if(res.data){
            res.data.forEach(val=>{
              val.hasChecked = false;
              val.allChecked = false;
              val.tagsChecked=[];
              val.tags.forEach(subVal=>{
                subVal.checked = false;
              })
            });
          }
          this.Alltags=res.data;
        })
      },
      selectAll(row) {
        row.allChecked = row.tagsChecked.length === row.tags.length;
        row.tagsChecked = [];
        row.tags.forEach(function(val){
          val.checked = !row.allChecked;
          if(val.checked)row.tagsChecked.push(val);
        });
        let tagsChecked = row.tagsChecked.length;
        row.allChecked = tagsChecked === row.tags.length;
        row.hasChecked = tagsChecked > 0 && tagsChecked < row.tags.length;
      },
      checkTag(row) {
        row.tagsChecked = [];
        row.tags.forEach(function(val){
          if(val.checked)row.tagsChecked.push(val);
        });
        let tagsChecked = row.tagsChecked.length;
        row.allChecked = tagsChecked === row.tags.length;
        row.hasChecked = tagsChecked > 0 && tagsChecked < row.tags.length;
      },
    }
  }
</script>
<style lang="less" scoped>
  #staticEchart{
    width:100%;
    min-height:35rem;
  }
  .LeaveRecord-title{
    margin-top:2rem;
  }
  .ArchivesStatistics{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .ArchivesStatistics .FilerecordPassed-checkbox{
    border: 1px solid #d1dbe5;
    border-radius: 3px;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,.12), 0 0 6px rgba(0,0,0,.04);
    box-sizing: border-box;
    margin:6px 0;
    z-index:1001;
    position: absolute;
    padding: 1rem;
  }
  .ArchivesStatistics  .el-checkbox-group .el-checkbox{
    margin-left: 0;
    margin-top: .6rem;
    margin-right: .6rem;
  }
  .ArchivesStatistics .LeaveRecord-search{
    border-radius: 1.1rem;
    padding-left: 1.6rem;
    padding-right:1.6rem;
  }
  .ArchivesStatistics .StaticsList .el-table th{
    background-color: #89bcf5;
    height:3.5rem;
  }
  .ArchivesStatistics .StaticsList .el-table td{
    height:3rem;
    font-size: .875rem;
  }
  .ArchivesStatistics .StaticsList .el-table__footer-wrapper thead div, .alertsList .el-table__header-wrapper thead div{
    background-color: #89bcf5;
    color: #fff;
  }
  .ArchivesStatistics .StaticsList .el-table th>.cell{
    background-color: #89bcf5;
    color: #fff;
  }
  .ArchivesStatistics .Hidecheckbox-btn{
    margin-top: 1.6rem;
    padding: .35rem 1rem;
  }
</style>
