<template>
    <div class="TeachingEvaluationRecord">
      <h3>教学评价记录</h3>
      <el-col :span="24" style="margin-top: 2rem">
        <el-col :span="17" class="alertsBtn" style="margin-top: 0">
          <el-button class="delete" title="导出" @click="download()">
            <img class="delete_unactive"
                 src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
                 alt="">
          </el-button>
          <el-button-group style="margin-left: 2.1rem">
            <el-button class="filt" title="复制" @click="operationData('copy')">
              <img class="filt_unactive"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
                   alt="">
              <img class="filt_active"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
                   alt="">
            </el-button>
            <el-button class="delete" title="打印" @click="operationData('print')">
              <img class="delete_unactive"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
                   alt="">
              <img class="delete_active"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
                   alt="">
            </el-button>
          </el-button-group>
        </el-col>
        <el-col :span="5" :offset="2" class="Infor-input-inner">
          <el-input style="border-radius:1rem" placeholder="请输入搜索内容" suffix-icon="el-icon-search" v-model="key" @change="handleIconClick"></el-input>
        </el-col>
      </el-col>
      <el-col :span="24">
        <el-row class="alertsList">
          <el-table
            :data="tableData"
            style="width: 100%"
            v-loading.body="isLoading"
            element-loading-text="拼命加载中...">
            <el-table-column
              prop="name"
              label="评教名称"
              align="center">
            </el-table-column>
            <el-table-column
              prop="startTime"
              label="评教开始日期"
              align="center">
              <template slot-scope="scope">
                <span>{{scope.row.startTime}}</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="endTime"
              label="评教结束时间"
              align="center">
              <template slot-scope="scope">
                <span>{{scope.row.endTime}}</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="createTime"
              label="评教人数"
              align="center">
              <template slot-scope="scope">
                <div class="grayDiv" style="cursor: pointer;" @click="showprogess(scope.row)">
                  <p class="grayDivSpan">{{scope.row.yet}}/{{scope.row.total}}</p>
                  <div class="greenDiv" :style="{width:scope.row.yet*100/scope.row.total+'%'}"></div>
                </div>
              </template>
            </el-table-column>
            <el-table-column
              prop="mode"
              label="评教方式"
              align="center">
              <template slot-scope="scope">
                <span v-if="scope.row.mode==='1'">分数</span>
                <span v-if="scope.row.mode==='2'">满意度</span>
                <span v-if="scope.row.mode==='3'">星级</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="createTime"
              label="创建时间"
              align="center">
              <template slot-scope="scope">
                <span>{{scope.row.createTime}}</span>
              </template>
            </el-table-column>
            <el-table-column
              label="操作"
              align="center">
              <template slot-scope="scope">
                <span style="color:#4da1ff;cursor: pointer;border-right:1px solid #d2d2d2;padding-right:.6rem" v-if="scope.row.publish==='0'" @click="publish(scope.row,1)">发布成绩</span>
                <span style="color:#48b6c4;border-right:1px solid #d2d2d2;padding:0 .6rem" v-if="scope.row.publish==='1'">已发布</span>
                <span style="color:#ff6a6a;cursor: pointer;padding-left: .6rem" @click="deleteRecord(scope.row)">删除</span>
              </template>
            </el-table-column>
          </el-table>
        </el-row>
        <el-dialog title="评教人数详情" v-if="showprocess" :modal="false" :visible.sync="showprocess">
          <el-row class="alertsList">
            <el-table
              :data="detailDate"
              style="width: 100%;height: 36rem;overflow-y: auto"
              border>
              <el-table-column
                prop="grade"
                label="年级"
                align="center"
                width="100">
              </el-table-column>
              <el-table-column
                prop="class"
                label="班级"
                align="center">
              </el-table-column>
              <el-table-column
                label="评教人数"
                align="center">
                <template slot-scope="scope">
                  <div class="grayDiv">
                    <p class="grayDivSpan">{{scope.row.yet}}/{{scope.row.total}}</p>
                    <div class="greenDiv" :style="{width:scope.row.yet*100/scope.row.total+'%'}"></div>
                  </div>
                </template>
              </el-table-column>
            </el-table>
          </el-row>
        </el-dialog>
        <el-row class="pageAlerts">
          <el-pagination
            @current-change="handleCurrentChange"
            :current-page.sync="currentPage"
            :page-size="10"
            layout="prev, pager, next, jumper"
            :total="total">
          </el-pagination>
        </el-row>
      </el-col>
    </div>
</template>
<script>
  import req from '@/assets/js/common'
  import formatdata from '@/assets/js/date'
    export default{
        data(){
            return {
              key:'',
              isLoading:false,
              tableData:[],
              detailDate:[],
              currentPage: 1,
              pageALl: 10,
              total:0,
              showprocess:false,
            }
        },
      created(){
        this.getRecordList();
      },
      methods:{
        operationData(type){
          let sAy = [], hdData = {
            name: '评教名称',
            startTime: '评教开始日期',
            endTime: '评教结束日期',
            newnum: '评教人数',
            Newmode: '评教方式',
            createTime: '创建时间',
          };
          sAy.push(hdData);
          for (let obj of this.tableData) {
            let d = {};
            for (let name in hdData) {
              this.tableData.forEach(val=>{
                return val.newnum=val.yet+'/'+val.total;
              });
              if(name==='Newmode'){
                if(obj.mode==1){
                  obj[name]='分数'
                }else if(obj.mode==2){
                  obj[name]='满意度'
                }
                else if(obj.mode==3){
                  obj[name]='星级'
                }
              }
              d[name] = obj[name] || '';
            }
            sAy.push(d);
          }
          if (type === 'copy') {
            req.copyTableData('.TeachingEvaluationRecord', sAy);
          } else {
            req.lodop(sAy);
          }
        },
        showprogess(row){
          this.detailDate=row.list[1];
          this.showprocess=true;
        },
        download(){
          req.downloadFile('.TeachingEvaluationRecord','/school/StudentEvaluate/recordEvaluate?export=ensure','post');
        },
        getRecordList(page){
          if(page!==this.currentPage){
            this.currentPage=page;
          }
          this.isLoading=true;
          let param={
            page:page,
            count:10,
            key:this.key
          };
          req.ajaxSend('/school/StudentEvaluate/recordEvaluate','post',param,(res)=>{
            if (res.status === -1) {
              this.tableData = [];
              this.isLoading = false;
              return;
            }
            this.tableData=res.data;
            this.total = res.total;
            this.isLoading=false;
          });
        },
        publish(row,status){
          if(row.yet!==row.total){
            this.vmMsgWarning( '考评未完成，不能发布成绩' ); return;
          }
          let param={
            type:'publish',
            id:parseInt(row.id),
            publish:status
          };
          this.$confirm('是否确定发布该数据?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/StudentEvaluate/recordEvaluate','post',param,(res)=>{
              if(res.status===1){
                this.vmMsgSuccess( res.msg );
              }else{
                this.vmMsgError( res.msg );
              }
              this.getRecordList();
            })
          }).catch(() => {

          });
        },
        deleteRecord(row){
          let param={
            type:'del',
            id:parseInt(row.id)
          };
          this.$confirm('是否确定删除该数据?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/StudentEvaluate/recordEvaluate','post',param,(res)=>{
              if(res.status){
                this.vmMsgSuccess( res.msg );
              }else{
                this.vmMsgError( res.msg );
              }
              this.getRecordList();
            })
          }).catch(() => {

          });
        },
        handleCurrentChange(val) {
          this.currentPage = val;
          this.getRecordList(val);
        },
        handleIconClick(){
          this.getRecordList();
        },
      }
    }
</script>
<style lang="less" scoped>
  .TeachingEvaluationRecord{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    .grayDiv{
      background-color: #F0F0F0;
      height:22/16rem;
      position: absolute;
      width: 90%;
      left: 5%;
      top: 8px;
    }
    .greenDiv{
      background-color: #13B5B1;
      height:22/16rem;
      position: relative;
      top:0;
      left:0;
    }
    .grayDivSpan{
      width: 100%;
      text-align: center;
      line-height: 18px;
      position: absolute;
      z-index: 10;
    }
    .Infor-input-inner .el-input__inner{
      border-radius: 1.1rem;
    }
  }
</style>
