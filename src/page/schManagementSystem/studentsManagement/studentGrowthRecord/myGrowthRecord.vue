<template>
  <div class="myGrowthRecord">
    <h3>我的成长记录</h3>
    <el-row :gutter="20" class="myGrowthRecord_row">
      <el-row class="treeList">
        <el-row class="myGrowthRecord_content">
          <el-row class="myGrowthRecord_mainContent">
            <el-row class="arrowContent">
              <img
                src="../../../../assets/img/schManagementSystem/studentsManagement/studentGrowthRecord/icon_arrow.png"
                alt="" class="arrowTop">
            </el-row>
            <el-row>
              <el-row class="lineDevice" v-for="(a,index) in tableData" :key="a.recordId">
                <el-col :span="5">
                  <el-row class="myGrowthRecord_content_row myGrowthRecord_content_l">
                    <p>{{a.createTime}}</p>
                    <p>{{a.teacherName}}老师 {{a.post}} {{a.subject}}</p>
                    <span class="circle"></span>
                  </el-row>
                </el-col>
                <el-col :span="19">
                  <el-row type="flex" align="middle">
                    <el-col :span="24" class="gap">
                      <el-row class="myGrowthRecord_content_row myGrowthRecord_content_center">
                        <el-row type="flex" align="middle">
                          <el-col :span="10">
                            <h5>{{a.title}}</h5>
                          </el-col>
                          <el-col :span="14">
                            <el-tag
                              v-for="tag in a.la"
                              :key="tag.labelId"
                            >
                              {{tag.labelName}}
                            </el-tag>
                          </el-col>
                        </el-row>
                        <el-row class="detail">
                          <p>{{a.content}}</p>
                          <div class="showImg">
                            <div v-for="(data,ix) in a.url" :key="ix" @click="showImg('open',index)">
                              <img :src="data" alt="">
                            </div>
                          </div>
                        </el-row>
                      </el-row>
                    </el-col>
                  </el-row>
                </el-col>
              </el-row>
            </el-row>
          </el-row>
        </el-row>
      </el-row>
    </el-row>
    <div class="fileImgCarousel" v-show="fileListState">
      <div class="fileImgCarousel_body">
        <span class="closeFileImgCarousel" @click="showImg('close')"><i class="el-icon-close"></i></span>
        <div class="fileImgCarousel_img">
          <el-carousel :autoplay="false" indicator-position="none">
            <el-carousel-item v-for="item in fileList" :key="item">
              <div>
                <img :src="item" alt="">
              </div>
            </el-carousel-item>
          </el-carousel>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default {
    data() {
      return {
        tableData: [],
        fileList: [],
        fileListState: false
      }
    },
    created: function () {
      var self = this;
      req.ajaxSend('/school/Studentsgrowthrecord/myRecord?type=getList','get','',function (res) {
        self.tableData = res.data;
      })
    },
    methods: {
      showImg(type, ix) {
        if (type == 'open') {
          this.fileListState = true;
          this.fileList = this.tableData[ix].url;
        } else {
          this.fileListState = false;
        }
      }
    }
  }
</script>
<style>
  .myGrowthRecord {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .myGrowthRecord h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
  }

  .myGrowthRecord .myGrowthRecord_row {
    margin-top: 3rem;
  }

  .myGrowthRecord .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
  }

  .myGrowthRecord h5 {
    font-size: 1rem;
  }

  .myGrowthRecord .myGrowthRecord_content {
    height: 49.125rem;
    overflow: auto;
    margin-top: 3rem;
  }

  .myGrowthRecord .lineDevice {
    margin: 3rem 0;
  }

  .myGrowthRecord .myGrowthRecord_mainContent {
    position: relative;
    min-height: 49.125rem;
  }

  .myGrowthRecord .myGrowthRecord_mainContent .gap {
    margin-left: 3rem;
    margin-right:1rem;
  }

  .myGrowthRecord .myGrowthRecord_mainContent .arrowContent {
    position: absolute;
    left: 20.8333%;
    height: 97%;
    width: .2rem;
    background-color: #dcdcdc;
    bottom: 0;
  }

  .myGrowthRecord .myGrowthRecord_mainContent .arrowTop {
    width: 1.75rem;
    position: absolute;
    top: -1.2rem;
    left: -.775rem;
  }

  .myGrowthRecord .myGrowthRecord_content_l {
    padding: 1rem;
  }

  .myGrowthRecord .myGrowthRecord_content_l .circle {
    position: absolute;
    width: 1.2rem;
    height: 1.2rem;
    border-radius: 100%;
    background: rgba(77, 161, 255, .5);
    top: 1rem;
    right: -.75rem;
  }

  .myGrowthRecord .myGrowthRecord_content_l .circle:before {
    display: block;
    content: '';
    width: .7rem;
    height: .7rem;
    background-color: #4da1ff;
    border-radius: 100%;
    position: absolute;
    top: 50%;
    left: 50%;
    -webkit-transform: translate(-50%, -50%);
    -moz-transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    -o-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
  }

  .myGrowthRecord .myGrowthRecord_content_row {
    position: relative;
  }

  .myGrowthRecord .myGrowthRecord_content_center {
    border: 1px solid #d2d2d2;
    padding: .5rem 1rem;
    border-radius: 5px;
    background-color: #fff;
  }

  .myGrowthRecord .myGrowthRecord_content_center .el-tag {
    margin: .2rem .625rem .2rem 0;
  }

  .myGrowthRecord .myGrowthRecord_content_center:before {
    display: block;
    content: '';
    position: absolute;
    width: .8rem;
    height: .8rem;
    background-color: #fff;
    border-left: 1px solid #d2d2d2;
    border-top: 1px solid #d2d2d2;
    -webkit-transform: rotate(-45deg);
    -moz-transform: rotate(-45deg);
    -ms-transform: rotate(-45deg);
    -o-transform: rotate(-45deg);
    transform: rotate(-45deg);
    left: -.5rem;
    top: 1rem;
  }

  .myGrowthRecord .myGrowthRecord_content_center .detail {
    margin: 1.5rem 0;
    line-height: 2;
  }

  .myGrowthRecord .el-tag {
    background-color: #f08bc5;
    margin-right: .6rem;
    color: #fff;
  }

  .myGrowthRecord .showImg > div {
    width: 10%;
    float: right;
    margin-left: 1rem;
  }

  .myGrowthRecord .showImg img {
    width: 100%;
    height: auto;
  }

  .myGrowthRecord .fileList > span {
    margin-right: 1rem;
    display: inline-block;
    width: 4rem;
    padding: 1rem;
    position: relative;
  }

  .myGrowthRecord .fileList > span img {
    width: 100%;
    height: auto;
  }

  .myGrowthRecord .fileList > span {
    margin-right: 1rem;
    display: inline-block;
    width: 4rem;
    padding: 1rem;
    position: relative;
  }

  .myGrowthRecord .fileList > span img {
    width: 100%;
    height: auto;
  }

  .myGrowthRecord .fileImgCarousel {
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    z-index: 66;
    position: fixed;
    background: rgba(0, 0, 0, .5);
  }

  .myGrowthRecord .fileImgCarousel_body {
    width: 360px;
    position: relative;
    left: 50%;
    top: 50%;
    -webkit-transform: translate(-50%, -50%);
    -moz-transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    -o-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
  }

  .myGrowthRecord .fileImgCarousel_body img {
    width: 100%;
    height: auto;
  }

  .myGrowthRecord .closeFileImgCarousel {
    position: absolute;
    top: 0;
    right: -60px;
    color: #fff;
    font-size: 25px;
    cursor: pointer;
  }
</style>
